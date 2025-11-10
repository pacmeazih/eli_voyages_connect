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
        // Guard: only attempt to alter contracts table if it exists (demo environment may not have it yet)
        if (Schema::hasTable('contracts')) {
            Schema::table('contracts', function (Blueprint $table) {
                if (!Schema::hasColumn('contracts', 'docuseal_submission_id')) {
                    $table->string('docuseal_submission_id')->nullable()->after('status');
                }
                if (!Schema::hasColumn('contracts', 'docuseal_template_id')) {
                    $table->string('docuseal_template_id')->nullable()->after('docuseal_submission_id');
                }
                if (!Schema::hasColumn('contracts', 'docuseal_signers')) {
                    $table->json('docuseal_signers')->nullable()->after('docuseal_template_id');
                }
                if (!Schema::hasColumn('contracts', 'sent_for_signature_at')) {
                    $table->timestamp('sent_for_signature_at')->nullable()->after('docuseal_signers');
                }
                if (!Schema::hasColumn('contracts', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('sent_for_signature_at');
                }
                if (!Schema::hasColumn('contracts', 'signed_document_path')) {
                    $table->string('signed_document_path')->nullable()->after('completed_at');
                }
                $table->index('docuseal_submission_id');
            });

            // Create contract_signatures table only if not exists and contracts table present
            if (!Schema::hasTable('contract_signatures')) {
                Schema::create('contract_signatures', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contracts')) {
            Schema::table('contracts', function (Blueprint $table) {
                $drops = [
                    'docuseal_submission_id',
                    'docuseal_template_id',
                    'docuseal_signers',
                    'sent_for_signature_at',
                    'completed_at',
                    'signed_document_path',
                ];
                foreach ($drops as $col) {
                    if (Schema::hasColumn('contracts', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        Schema::dropIfExists('contract_signatures');
    }
};
