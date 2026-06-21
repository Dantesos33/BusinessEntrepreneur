<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_details', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->text('investment_interests')->nullable(); // JSON array, cast on the model
            $table->text('investment_stage')->nullable();     // JSON array
            $table->text('portfolio_companies')->nullable();  // JSON array
            $table->unsignedInteger('total_investments')->default(0);
            $table->string('minimum_investment')->nullable();
            $table->string('maximum_investment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_details');
    }
};
