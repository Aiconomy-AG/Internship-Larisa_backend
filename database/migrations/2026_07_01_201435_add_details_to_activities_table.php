<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // tip: 'obiectiv' (vizite/monumente), 'gastronomie' (restaurante/cafenele) sau 'altele'
            $table->string('tip')->default('altele')->after('titlu_activitate');
            // descriere: notițe libere despre activitate (ex: adresă, recomandări, rezervare)
            $table->text('descriere')->nullable()->after('tip');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['tip', 'descriere']);
        });
    }
};