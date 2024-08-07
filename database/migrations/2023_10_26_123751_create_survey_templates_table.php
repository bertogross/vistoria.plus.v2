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
        Schema::create('survey_templates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('title', 191)->nullable();
            $table->longText('description')->nullable();
            $table->enum('model', ['default', 'custom', 'both'])->default('both');
            $table->json('template_data')->nullable();
            $table->enum('condition_of', ['publish', 'filed', 'deleted'])->default('publish');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_templates');
    }
};
