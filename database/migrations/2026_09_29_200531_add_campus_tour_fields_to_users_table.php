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
            $table->string('school_name')->nullable()->after('email');
            $table->foreignId('profession_id')->nullable()->after('school_name')->constrained()->nullOnDelete();
            $table->boolean('is_admin')->default(false)->after('profession_id');
            $table->unsignedInteger('points')->default(0)->after('is_admin');
            $table->timestamp('campus_tour_completed_at')->nullable()->after('points');

            $table->index('school_name');
            $table->index(['profession_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['school_name']);
            $table->dropIndex(['profession_id', 'created_at']);
            $table->dropConstrainedForeignId('profession_id');
            $table->dropColumn(['school_name', 'is_admin', 'points', 'campus_tour_completed_at']);
        });
    }
};
