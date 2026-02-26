<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_unlock_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('month')->comment('First day of the locked month (Y-m-01)');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });

        Schema::table('report_unlock_requests', function (Blueprint $table) {
            $table->unique(['user_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_unlock_requests');
    }
};
