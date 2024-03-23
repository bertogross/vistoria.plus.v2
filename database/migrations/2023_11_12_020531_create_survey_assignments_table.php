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
        Schema::create('survey_assignments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('survey_id')->nullable();
            $table->bigInteger('company_id')->default(0);
            $table->bigInteger('surveyor_id')->nullable()->comment('The surveyor user_id');
            $table->bigInteger('auditor_id')->nullable()->comment('The auditor user_id');
            $table->enum('surveyor_status', ['new', 'pending', 'in_progress', 'auditing', 'completed', 'losted'])->default('new')->comment('The status of the survey task for this surveyor');
            $table->enum('auditor_status', ['waiting', 'new', 'pending', 'in_progress', 'completed', 'losted', 'bypass'])->nullable()->comment('The status of the survey task for this auditor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_assignments');
    }
};
