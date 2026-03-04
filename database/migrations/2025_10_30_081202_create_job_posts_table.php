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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();

            // Basic Job Information
            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();

            // Job Details
            $table->string('department')->nullable();
            $table->string('position_type')->default('full-time'); // full-time, part-time, contract, remote
            $table->string('experience_level')->nullable(); // entry, mid, senior, executive
            $table->decimal('salary_range_min', 10, 2)->nullable();
            $table->decimal('salary_range_max', 10, 2)->nullable();
            $table->string('location')->nullable();

            // Application Process
            $table->date('application_deadline')->nullable();
            $table->integer('vacancies')->default(1);
            $table->boolean('is_remote')->default(false);

            // Status & Workflow
            $table->enum('status', ['draft', 'published', 'closed', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Exam Configuration
            $table->integer('exam_id')->nullable();
            $table->integer('exam_duration')->nullable()->comment('Duration in minutes');
            $table->decimal('passing_score', 5, 2)->nullable()->comment('Percentage required to pass');

            // Ownership
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('status');
            $table->index('application_deadline');
            $table->index(['status', 'published_at']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
