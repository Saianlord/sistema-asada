<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('approval_agreement')->nullable()->after('evidence_path');
            $table->date('approval_date')->nullable()->after('approval_agreement');
            $table->string('approval_responsible')->nullable()->after('approval_date');
            $table->text('approval_justification')->nullable()->after('approval_responsible');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['approval_agreement', 'approval_date', 'approval_responsible', 'approval_justification']);
        });
    }
};
