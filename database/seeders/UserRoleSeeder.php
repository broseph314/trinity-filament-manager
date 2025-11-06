<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions once
        $perms = [
            'characters.view.own',
            'characters.view.any',
            'teleport.own',
            'teleport.any',
            'grant.items.own',
            'grant.items.any',
            'grant.gold.own',
            'grant.gold.any',
            'mail.send.any',
            'system.cmd',
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p);
        }

        // Roles (bundles)
        $player = Role::findOrCreate('player');
        $player->syncPermissions([
            'characters.view.own',
        ]);

        $playerPlus = Role::findOrCreate('player-plus');
        $playerPlus->syncPermissions([
            'characters.view.own',
            'teleport.own',
        ]);

        $modSelf = Role::findOrCreate('moderator-self');
        $modSelf->syncPermissions([
            'characters.view.own',
            'teleport.own',
            'grant.items.own',
            'grant.gold.own',
        ]);

        $mod = Role::findOrCreate('moderator');
        $mod->syncPermissions([
            'characters.view.any',
            'teleport.any',
            'grant.items.any',
            'grant.gold.any',
        ]);

        $gm = Role::findOrCreate('gm');
        $gm->syncPermissions([
            'characters.view.any',
            'teleport.any',
            'grant.items.any',
            'grant.gold.any',
            'mail.send.any',
            // intentionally not giving system.cmd to GM
        ]);

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions(Permission::all());
    }
}
