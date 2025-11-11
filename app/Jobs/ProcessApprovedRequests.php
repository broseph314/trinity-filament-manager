<?php

namespace App\Jobs;

use App\Models\Request;
use App\Models\RequestLine;
use App\Services\Trinity\CommandService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class ProcessApprovedRequests implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CommandService $svc): void {
        $req = Request::with('lines')->where('status','approved')->oldest()->first();
        if (! $req) return;

        $req->update(['status'=>'processing']);

        foreach ($req->lines as $line) {
            if ($line->status === 'done') continue;

            try {
                $this->dispatchLine($svc, $req, $line);
                $line->update(['status'=>'done','processed_at'=>now(),'error_text'=>null]);
                usleep(250_000); // throttle
            } catch (\Throwable $e) {
                $line->update([
                    'status' => $line->attempts >= 5 ? 'error' : 'pending',
                    'attempts' => $line->attempts + 1,
                    'error_text' => Str::of($e->getMessage())->limit(500),
                ]);
            }
        }

        $fresh = $req->fresh('lines');
        if ($fresh->lines()->where('status','!=','done')->count() === 0) {
            $fresh->update(['status'=>'done','processed_at'=>now()]);
        } elseif ($fresh->lines()->where('status','error')->exists()) {
            $fresh->update(['status'=>'failed']);
        } else {
            // still has pending (will retry next run)
            $fresh->update(['status'=>'processing']);
        }
    }

    private function dispatchLine(CommandService $svc, Request $req, RequestLine $line): void {
        $name = $req->character_name;
        $p    = (array) $line->params;

        match ($line->action) {
            'send_money' => $svc->run(
                sprintf('send money %s "Approved Request" "Enjoy!" %d', $name, (int)$p['copper'])
            ),

            'send_item' => $svc->run(
                sprintf('send items %s "Approved Request" "Items attached." %d:%d',
                    $name, (int)$p['entry'], (int)$p['qty'])
            ),

            'set_level' => $svc->run(
                sprintf('level %s %d', $name, (int)$p['level'])
            ),

            'teleport' => $svc->run(
                sprintf('tele name %s "%s"', $name, trim($p['location']))
            ),

            'grant_perm' => $this->grantPermission($name, $p, $svc),

            default => throw new \RuntimeException("Unknown action {$line->action}"),
        };
    }

    private function grantPermission(string $name, array $p, CommandService $svc): void {
        // map your internal perm keys to GM commands
        $map = [
            'mount_310' => '.learn 34091', // example
        ];
        if (!isset($map[$p['perm']])) {
            throw new \RuntimeException('Unknown permission key');
        }
        $svc->run(sprintf('%s "%s"', $map[$p['perm']], $name));
    }

}
