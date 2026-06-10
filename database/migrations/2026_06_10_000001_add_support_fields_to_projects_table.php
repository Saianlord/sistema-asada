<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('technical_justification')->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->text('impact')->nullable();
            $table->text('risk')->nullable();
            $table->string('evidence_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'technical_justification',
                'estimated_cost',
                'impact',
                'risk',
                'evidence_path',
            ]);
        });
    }
};
