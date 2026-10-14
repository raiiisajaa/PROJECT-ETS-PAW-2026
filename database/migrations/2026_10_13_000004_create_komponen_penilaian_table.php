<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Komponen penilaian per mata kuliah beserta bobotnya.
     * Bobot disimpan sebagai desimal (0,20 = 20%) sesuai PRD (decimal 3,2).
     */
    public function up(): void
    {
        Schema::create('komponen_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->decimal('bobot', 3, 2);
            $table->timestamps();

            $table->unique(['mata_kuliah_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_penilaian');
    }
};
