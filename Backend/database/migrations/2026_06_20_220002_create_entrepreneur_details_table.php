<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrepreneur_details', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->string('startup_name')->nullable();
            $table->text('pitch_summary')->nullable();
            $table->string('funding_needed')->nullable();
            $table->string('industry')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->unsignedInteger('team_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrepreneur_details');
    }
};
