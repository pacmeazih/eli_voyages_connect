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
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'nom')) {
                $table->string('nom')->nullable()->after('civilite');
            }
            if (!Schema::hasColumn('clients', 'prenom')) {
                $table->string('prenom')->nullable()->after('nom');
            }
            if (!Schema::hasColumn('clients', 'adresse')) {
                $table->text('adresse')->nullable()->after('prenom');
            }
            if (!Schema::hasColumn('clients', 'telephone')) {
                $table->string('telephone')->nullable()->after('adresse');
            }
            if (!Schema::hasColumn('clients', 'email')) {
                $table->string('email')->nullable()->after('telephone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['nom', 'prenom', 'adresse', 'telephone', 'email']);
        });
    }
};
