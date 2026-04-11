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
        Schema::create('sk_officials', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('municipality_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('barangay_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Role
            $table->string('role'); // 'SKMF Chairperson', 'SK Chairperson', 'SK Member', 'SK Secretary', 'SK Treasurer'
            $table->string('committee')->nullable();
            
            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('facebook_url')->nullable();
            
            // Current Address
            $table->string('current_house_no')->nullable();
            $table->foreignId('current_barangay_id')->nullable()->constrained('barangays');
            $table->foreignId('current_municipality_id')->nullable()->constrained('municipalities');
            
            // Permanent Address
            $table->boolean('is_same_as_current_address')->default(true);
            $table->string('permanent_house_no')->nullable();
            $table->foreignId('permanent_barangay_id')->nullable()->constrained('barangays');
            $table->foreignId('permanent_municipality_id')->nullable()->constrained('municipalities');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_officials');
    }
};
