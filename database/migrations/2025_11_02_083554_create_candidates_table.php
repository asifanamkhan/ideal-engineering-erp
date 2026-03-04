<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_create_candidates_table.php

    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            // Core & System Fields
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('status');

            // Recommended Fields
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->date('date_of_birth')->nullable();

            // Professional Fields
            $table->string('current_company')->nullable();
            $table->string('current_position')->nullable();
            $table->string('total_experience')->nullable(); // Fixed: removed space
            $table->string('current_salary')->nullable();
            $table->string('expected_salary')->nullable();
            $table->string('linkedin_profile')->nullable();
            $table->string('github_profile')->nullable();
            $table->string('portfolio_website')->nullable();

            // Application Fields
            $table->string('resume_path')->nullable();
            $table->string('job_id')->nullable();

            // Add these missing fields from your form
            $table->string('gender')->nullable();
            $table->string('national_id')->nullable(); // For NID/Birth Certificate/Passport

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
