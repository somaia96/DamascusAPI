<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('council_members', function (Blueprint $table) {
            $table->unsignedBigInteger('photo_id')->nullable()->after('name'); // Add the photo_id column
        });
    }

    public function down(): void
    {
        Schema::table('council_members', function (Blueprint $table) {
            $table->dropColumn('photo_id'); // Drop the photo_id column if needed
        });
    }
};
