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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('surveyor_id')->nullable();
            $table->bigInteger('auditor_id')->nullable();
            $table->bigInteger('step_id')->nullable();
            $table->bigInteger('topic_id')->nullable();
            $table->bigInteger('survey_id')->nullable();
            $table->bigInteger('assignment_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->enum('compliance_survey', ['yes', 'no'])->nullable();
            $table->enum('compliance_audit', ['yes', 'no'])->nullable();
            $table->text('comment_survey')->nullable();
            $table->text('comment_audit')->nullable();
            $table->json('attachments_survey')->nullable();
            $table->json('attachments_audit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
