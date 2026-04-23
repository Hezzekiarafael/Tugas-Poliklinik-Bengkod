<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('jadwal_periksa', function (Blueprint $table) {
        // Kita definisikan ulang enum-nya dengan menyertakan 'Rabu'
        $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->change();
    });
}

public function down()
{
    Schema::table('jadwal_periksa', function (Blueprint $table) {
        $table->enum('hari', ['Senin', 'Selasa', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->change();
    });
}
};
