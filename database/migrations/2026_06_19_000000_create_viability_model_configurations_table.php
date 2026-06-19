<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viability_model_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('technical_weight')->default(25);
            $table->unsignedInteger('financial_weight')->default(25);
            $table->unsignedInteger('operational_weight')->default(25);
            $table->unsignedInteger('regulatory_weight')->default(25);
            $table->decimal('viable_threshold', 5, 2)->default(7.00);
            $table->decimal('conditional_threshold', 5, 2)->default(4.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viability_model_configurations');
    }
};
