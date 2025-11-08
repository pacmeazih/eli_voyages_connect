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
            $table->string('civilite')->nullable()->after('id');
            $table->date('date_naissance')->nullable()->after('email');
            $table->string('lieu_naissance')->nullable()->after('date_naissance');
            $table->string('nationalite')->nullable()->after('lieu_naissance');
            $table->string('profession')->nullable()->after('nationalite');
            $table->string('passeport_numero')->nullable()->after('profession');
            $table->date('passeport_date_emission')->nullable()->after('passeport_numero');
            $table->date('passeport_date_expiration')->nullable()->after('passeport_date_emission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'civilite',
                'date_naissance',
                'lieu_naissance',
                'nationalite',
                'profession',
                'passeport_numero',
                'passeport_date_emission',
                'passeport_date_expiration'
            ]);
        });
    }
};
