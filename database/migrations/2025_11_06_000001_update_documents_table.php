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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('name')->after('dossier_id')->nullable();
            $table->string('type')->after('name')->default('general');
            $table->text('description')->after('mime')->nullable();
            $table->foreignId('previous_version_id')->after('version')->nullable()
                ->constrained('documents')->nullOnDelete();
            
            // Rename columns
            $table->renameColumn('mime', 'mime_type');
            $table->renameColumn('filename', 'original_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['name', 'type', 'description', 'previous_version_id']);
            $table->renameColumn('mime_type', 'mime');
            $table->renameColumn('original_filename', 'filename');
        });
    }
};
