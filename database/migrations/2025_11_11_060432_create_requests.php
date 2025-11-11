<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('character_guid');
            $t->string('character_name');
            $t->string('category')->index();     // 'items','money','level','pack','perm', ...
            $t->enum('status',['pending','approved','rejected','processing','done','failed'])->default('pending');
            $t->string('reject_reason')->nullable();
            $t->json('meta')->nullable();        // freeform: player note, realmId override, etc.
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('request_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_id')->constrained()->cascadeOnDelete();
            $t->string('action');              // e.g. 'send_item','send_money','set_level','grant_perm'
            $t->json('params');                // e.g. {"entry":6948,"qty":1} or {"copper":10000}
            $t->enum('status',['pending','processing','done','error'])->default('pending');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->string('error_text', 512)->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
            $t->index(['request_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
        Schema::dropIfExists('request_lines');
    }
};
