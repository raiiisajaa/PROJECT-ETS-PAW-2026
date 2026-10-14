<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nilai tiap mahasiswa per komponen. Unik pada pasangan mahasiswa + komponen.
     */
    public function up(): void
    {
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('komponen_id')->constrained('komponen_penilaian')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2);
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'komponen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};
