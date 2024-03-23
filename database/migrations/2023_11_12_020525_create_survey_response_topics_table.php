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
        Schema::create('survey_response_topics', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('survey_id')->nullable();
            $table->bigInteger('step_id')->nullable();
            $table->string('question', 191)->nullable();
            $table->integer('topic_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_topics');
    }
};
