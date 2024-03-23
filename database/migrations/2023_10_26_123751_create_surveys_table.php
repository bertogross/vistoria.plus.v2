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
        Schema::create('surveys', function (Blueprint $table) {
            $status = ['new', 'scheduled', 'started', 'stopped', 'completed', 'filed'];

            $table->id();
            $table->bigInteger('parent_id')->default(0);
            $table->bigInteger('user_id')->nullable();
            $table->text('title')->default('Necessário Inserir um Título');
            $table->json('companies')->nullable();
            $table->bigInteger('template_id')->default(0);
            $table->enum('status', $status)->default('new')->comment("The status of the survey task.");
            $table->enum('old_status', $status)->default('new')->comment('The previous status of the survey task');
            $table->enum('priority', ['high', 'medium', 'low'])->default('high')->comment('The priority of the survey task');
            $table->enum('recurring', ['once', 'daily', 'weekly', 'biweekly', 'monthly', 'annual'])->default('daily');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_in')->nullable();
            $table->enum('model', ['default', 'custom', 'both'])->default('both');
            $table->json('distributed_data')->nullable()->comment('The json data to to use to populate the _assigments');
            $table->json('template_data')->nullable()->comment('The json data from survey_templates table');
            $table->timestamp('completed_at')->nullable()->comment('The timestamp when the survey task was completed');
            $table->timestamp('audited_at')->nullable()->comment('The timestamp when the survey task was audited');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
