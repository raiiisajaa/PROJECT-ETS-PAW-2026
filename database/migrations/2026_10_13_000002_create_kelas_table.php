<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kelas (rombel) untuk setiap mata kuliah.
     */
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('nama_kelas', 50);
            $table->timestamps();

            $table->unique(['mata_kuliah_id', 'nama_kelas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
