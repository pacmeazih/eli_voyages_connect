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
        Schema::table('dossiers', function (Blueprint $table) {
            if (!Schema::hasColumn('dossiers', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('dossiers', 'status')) {
                $table->string('status')->default('draft')->after('description');
            }
            if (!Schema::hasColumn('dossiers', 'package_id')) {
                $table->foreignId('package_id')->nullable()->constrained()->onDelete('set null')->after('client_id');
            }
            if (!Schema::hasColumn('dossiers', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn(['description', 'status', 'package_id', 'assigned_to']);
        });
    }
};
