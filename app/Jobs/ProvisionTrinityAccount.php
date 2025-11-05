<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Trinity\AccountProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionTrinityAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function handle(AccountProvisioner $provisioner): void
    {
        // idempotent: AccountProvisioner->provision() uses updateOrCreate on link table
        $provisioner->provision($this->user);
    }
}
