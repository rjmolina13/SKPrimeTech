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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_definition_id')->constrained()->cascadeOnDelete();
            
            // Polymorphic relation to the entity that submitted this (Barangay or Municipality)
            $table->string('record_type');
            $table->unsignedBigInteger('record_id');
            $table->index(['record_type', 'record_id']);
            
            // The actual form data (JSON string)
            $table->longText('data');
            
            // Who submitted it
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            
            $table->timestamps();
            
            $table->string('status')->default('submitted'); // draft, submitted, under_review, approved, rejected
            $table->text('remarks')->nullable(); // For rejection reasons
            $table->string('guid')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
