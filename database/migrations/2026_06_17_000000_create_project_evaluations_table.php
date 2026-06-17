<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('technical_score');
            $table->unsignedTinyInteger('financial_score');
            $table->unsignedTinyInteger('operational_score');
            $table->unsignedTinyInteger('regulatory_score');
            $table->decimal('average_score', 5, 2)->nullable();
            $table->string('viability_status')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_evaluations');
    }
};
