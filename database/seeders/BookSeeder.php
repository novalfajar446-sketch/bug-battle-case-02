<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Category;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        // Data uji untuk membuktikan relasi satu-kategori-banyak-buku:
        // kategori Teknologi sengaja dipakai oleh 3 buku sekaligus.
        $technology = Category::firstOrCreate(['name' => 'Teknologi']);
        $accounting = Category::firstOrCreate(['name' => 'Akuntansi']);
        $management = Category::firstOrCreate(['name' => 'Manajemen']);

        $rows = [
            ['Laravel untuk Pemula', 'Andi', $technology->id],
            ['Belajar Python', 'Budi', $technology->id],
            ['Pemrograman Web Modern', 'Citra', $technology->id],
            ['Pengantar Akuntansi', 'Dewi', $accounting->id],
            ['Akuntansi Biaya', 'Eko', $accounting->id],
            ['Manajemen Keuangan', 'Fitri', $management->id],
        ];

        foreach ($rows as [$title, $author, $categoryId]) {
            Book::firstOrCreate(
                ['title' => $title],
                ['author' => $author, 'category_id' => $categoryId]
            );
        }
    }
}
