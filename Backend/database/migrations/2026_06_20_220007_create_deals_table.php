<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrepreneur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('investor_id')->constrained('users')->cascadeOnDelete();
            $table->string('amount');
            $table->string('equity')->nullable();
            $table->enum('status', ['Due Diligence', 'Term Sheet', 'Negotiation', 'Closed', 'Passed'])
                ->default('Negotiation');
            $table->string('stage')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
