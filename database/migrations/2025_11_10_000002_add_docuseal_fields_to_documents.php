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
            $table->string('status')->nullable()->after('type');
            $table->string('docuseal_submission_id')->nullable()->after('path');
            $table->unsignedBigInteger('docuseal_template_id')->nullable()->after('docuseal_submission_id');
            $table->json('docuseal_signers')->nullable()->after('docuseal_template_id');
            $table->timestamp('sent_for_signature_at')->nullable()->after('docuseal_signers');
            $table->timestamp('completed_at')->nullable()->after('sent_for_signature_at');
            $table->string('signed_document_path')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'docuseal_submission_id',
                'docuseal_template_id',
                'docuseal_signers',
                'sent_for_signature_at',
                'completed_at',
                'signed_document_path',
            ]);
        });
    }
};
