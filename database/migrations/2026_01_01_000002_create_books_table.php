<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');

            // Perbaikan: category_id kini memakai foreign key sehingga
            // relasi satu kategori ke banyak buku benar-benar dijamin di level
            // database. Versi awal hanya kolom unsignedBigInteger tanpa
            // constraint, sehingga tidak ada jaminan integritas data.
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
