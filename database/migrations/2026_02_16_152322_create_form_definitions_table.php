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
        Schema::create('form_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->string('name');
            $table->json('schema');
            $table->string('scope')->default('both'); // barangay, municipality, both
            $table->boolean('is_active')->default(true);
            $table->timestamp('deadline')->nullable();
            $table->string('frequency')->default('quarterly'); // quarterly, annual, one-time
            $table->string('frequency_option')->nullable();
            $table->string('guid')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_definitions');
    }
};
