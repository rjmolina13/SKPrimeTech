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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities')->nullOnDelete();
            $table->string('avatar_url')->nullable()->after('municipality_id');
            $table->string('avatar_type')->default('upload')->after('avatar_url'); // 'upload' or 'generated'
            $table->string('avatar_background')->nullable()->after('avatar_type'); // hex code for generated
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('municipality_id');
            $table->dropColumn(['avatar_url', 'avatar_type', 'avatar_background']);
        });
    }
};
