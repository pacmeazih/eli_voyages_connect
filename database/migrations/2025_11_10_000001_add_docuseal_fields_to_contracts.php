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
        // Add DocuSeal columns to contracts table
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('docuseal_submission_id')->nullable()->after('status');
            $table->string('docuseal_template_id')->nullable()->after('docuseal_submission_id');
            $table->json('docuseal_signers')->nullable()->after('docuseal_template_id');
            $table->timestamp('sent_for_signature_at')->nullable()->after('docuseal_signers');
            $table->timestamp('completed_at')->nullable()->after('sent_for_signature_at');
            $table->string('signed_document_path')->nullable()->after('completed_at');
            
            $table->index('docuseal_submission_id');
        });

        // Create contract_signatures table
        Schema::create('contract_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->integer('docuseal_submitter_id')->nullable();
            $table->string('signer_email');
            $table->string('signer_name');
            $table->string('signer_role'); // 'client', 'guarantor', 'agent'
            $table->enum('status', ['pending', 'sent', 'opened', 'signed', 'declined'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['contract_id', 'status']);
            $table->index('signer_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'docuseal_submission_id',
                'docuseal_template_id',
                'docuseal_signers',
                'sent_for_signature_at',
                'completed_at',
                'signed_document_path',
            ]);
        });

        Schema::dropIfExists('contract_signatures');
    }
};
