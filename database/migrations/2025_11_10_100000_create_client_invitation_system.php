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
        // Add client_id to users table if not exists
        if (!Schema::hasColumn('users', 'client_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->onDelete('cascade');
                $table->index('client_id');
            });
        }

        // Add auto-generated client identifier to clients table
        if (!Schema::hasColumn('clients', 'client_code')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('client_code', 20)->unique()->after('id')->nullable();
                $table->index('client_code');
            });
        }

        // Create client_invitations table
        Schema::create('client_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone');
            $table->string('client_code', 20)->unique(); // Auto-generated: EV-YYYY-XXXX
            $table->string('invitation_token')->unique();
            $table->enum('status', ['pending', 'sent', 'accepted', 'expired'])->default('pending');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable(); // Store additional info
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index('invited_by');
        });

        // Add consultant assignment to documents table
        if (Schema::hasTable('documents') && !Schema::hasColumn('documents', 'consultant_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->foreignId('consultant_id')->nullable()->after('uploaded_by')->constrained('users')->onDelete('set null');
                $table->foreignId('assigned_by')->nullable()->after('consultant_id')->constrained('users')->onDelete('set null');
                $table->timestamp('consultant_signed_at')->nullable()->after('completed_at');
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
                $table->text('rejection_reason')->nullable()->after('approval_status');
                $table->foreignId('approved_by')->nullable()->after('rejection_reason')->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_invitations');

        if (Schema::hasColumn('users', 'client_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            });
        }

        if (Schema::hasColumn('clients', 'client_code')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('client_code');
            });
        }

        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                $columns = ['consultant_id', 'assigned_by', 'consultant_signed_at', 'approval_status', 'rejection_reason', 'approved_by', 'approved_at'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('documents', $col)) {
                        if (in_array($col, ['consultant_id', 'assigned_by', 'approved_by'])) {
                            $table->dropForeign(['documents_' . $col . '_foreign']);
                        }
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
