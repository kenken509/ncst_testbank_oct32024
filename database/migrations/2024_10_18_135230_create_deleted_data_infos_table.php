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
        Schema::create('deleted_data_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_log_id')->nullable();
            $table->enum('info_type',['question','user'])->nullable();
            $table->string('question_id')->nullable();
            $table->string('question_question')->nullable();
            $table->string('option_a')->nullable();
            $table->string('option_b')->nullable();
            $table->string('option_c')->nullable();
            $table->string('option_d')->nullable();
            $table->string('correct_answer')->nullable();
            $table->string('question_type')->nullable();
            $table->string('question_attached_image')->nullable();
            $table->string('question_term')->nullable();
            $table->string('subject_code')->nullable();
            $table->string('question_author_email')->nullable();
            $table->date('question_created_at')->nullable();
            $table->date('question_updated_at')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('user_department')->nullable();
            $table->string('user_division')->nullable();
            $table->date('user_created_at')->nullable();
            $table->date('user_updated_at')->nullable();
            
            $table->timestamps();

            $table->foreign('activity_log_id')->references('id')->on('activity_logs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_data_infos');
    }
};
