<?php

namespace App\Services\Trinity;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class SoapClient
{
    public function __construct(
        private readonly string $host,
        private readonly string $user,
        private readonly string $pass,
        private readonly int $timeout = 8,
        private readonly bool $verify = true,
    ) {}

    public static function makeFromConfig(): self
    {
        $cfg = config('trinity.soap');
        return new self($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['timeout'], $cfg['verify']);
    }

    /** Send a raw Trinity command and return the string inside <return>...</return>. */
    public function command(string $cmd): string
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
  <SOAP-ENV:Body>
    <ns1:executeCommand xmlns:ns1="urn:TC">
      <command>{$this->xmlEscape($cmd)}</command>
    </ns1:executeCommand>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;

        try {
            $res = Http::withBasicAuth($this->user, $this->pass)
                ->timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=utf-8',
                    // Some cores accept 'urn:TC' or empty; this variant is widely compatible:
                    'SOAPAction'   => 'urn:TC#executeCommand',
                ])
                ->withOptions(['verify' => $this->verify])
                ->send('POST', rtrim($this->host, '/'), ['body' => $xml]);

            $res->throw();

            $body = $res->body();

            // Try to extract <return>...</return>
            if (preg_match('#<return>(.*?)</return>#s', $body, $m)) {
                $clean = $this->normalizeReturn($m[1]);
                return $clean;
            }

            // If there is a SOAP Fault, surface it clearly
            if (preg_match('#<faultstring>(.*?)</faultstring>#s', $body, $m)) {
                throw new \RuntimeException('SOAP Fault: ' . trim($m[1]));
            }

            // Fallback: raw body
            return trim($body);
        } catch (RequestException $e) {
            // 401/403 etc.
            throw new \RuntimeException('SOAP HTTP error: ' . $e->getMessage(), previous: $e);
        } catch (ConnectionException $e) {
            throw new \RuntimeException('SOAP connection error (port/firewall?): ' . $e->getMessage(), previous: $e);
        }
    }

    private function xmlEscape(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function normalizeReturn(string $s): string
    {
        // Decode entities and strip ANSI color codes
        $s = html_entity_decode($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $s = preg_replace('/\x1B\[[0-9;]*m/', '', $s);          // ANSI
        $s = preg_replace("/\r\n?/", "\n", $s);                 // newlines
        $s = preg_replace("/[ \t]+\n/", "\n", $s);              // trim end spaces per line
        return trim($s);
    }
}
