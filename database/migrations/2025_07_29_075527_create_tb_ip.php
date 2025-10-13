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
        Schema::create('tb_ip', function (Blueprint $table) {
            $table->id();
            $table->string('vlan', 30)->nullable();
            $table->string('ip', 30)->nullable();
            $table->string('ip_services', 30)->nullable();
            $table->smallInteger('isdeleted')->default(0);
            $table->text('remark')->nullable();
            $table->string('iby', 100)->nullable();
            $table->timestamp('idt')->useCurrent();
            $table->string('uby', 100)->nullable();
            $table->timestamp('udt')->nullable();
            $table->string('dby', 30)->nullable();
            $table->timestamp('ddt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ip');
    }
};
