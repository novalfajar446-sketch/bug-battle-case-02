<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCategoryRelationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Gejala 1: "Migrasi tabel buku dapat gagal atau relasinya tidak terjamin."
     * Penyebab: kolom category_id tidak punya foreign key constraint.
     */
    public function test_foreign_key_category_terpasang_di_tabel_books()
    {
        $foreignKeys = \Illuminate\Support\Facades\Schema::getForeignKeys('books');

        $this->assertNotEmpty($foreignKeys, 'Tabel books tidak punya foreign key.');

        // Nama kolom pada hasil getForeignKeys() berbeda tiap driver
        // (MySQL vs SQLite), jadi periksa nilainya, bukan nama key-nya.
        $menyebutCategoryId = collect($foreignKeys)->contains(function ($foreignKey) {
            $values = array_map(
                fn ($value) => is_scalar($value) ? (string) $value : '',
                \Illuminate\Support\Arr::flatten((array) $foreignKey)
            );

            return in_array('category_id', $values, true);
        });

        $this->assertTrue(
            $menyebutCategoryId,
            'Foreign key category_id tidak ditemukan di tabel books.'
        );
    }

    public function test_menyimpan_buku_dengan_kategori_tidak_ada_ditolak_database()
    {
        // Test ini hanya bermakna kalau driver database benar-benar menegakkan
        // foreign key. SQLite hanya menegakkannya bila pragma-nya aktif.
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $pragma = \Illuminate\Support\Facades\DB::select('PRAGMA foreign_keys');
            if (! (int) (is_array($pragma[0]) ? ($pragma[0]['foreign_keys'] ?? 0) : reset($pragma))) {
                $this->markTestSkipped('SQLite tidak menegakkan foreign key pada koneksi ini.');
            }
        }

        $this->expectException(\Illuminate\Database\QueryException::class);

        Book::create([
            'title' => 'Buku Tanpa Kategori',
            'author' => 'Andi',
            'category_id' => 999, // id kategori yang tidak ada
        ]);
    }

    /**
     * Gejala 2: "Daftar buku gagal mengambil kategori."
     * Penyebab: relasi terbalik (hasMany/belongsTo) + with('categories')
     * yang tidak sesuai dengan nama relasi.
     */
    public function test_relasi_book_memiliki_satu_kategori()
    {
        $category = Category::create(['name' => 'Teknologi']);
        $book = Book::create([
            'title' => 'Laravel untuk Pemula',
            'author' => 'Andi',
            'category_id' => $category->id,
        ]);

        // belongsTo: satu buku -> satu kategori
        $this->assertInstanceOf(Category::class, $book->category);
        $this->assertSame('Teknologi', $book->category->name);
        $this->assertTrue($book->category->is($category));
    }

    /**
     * Tugas: "Pastikan satu kategori dapat memiliki banyak buku."
     * Penyebab: Category::books() sebelumnya memakai belongsTo() sehingga
     * kategori hanya bisa mengambil satu buku.
     */
    public function test_satu_kategori_dapat_memiliki_banyak_buku()
    {
        $category = Category::create(['name' => 'Teknologi']);

        $a = Book::create(['title' => 'Laravel untuk Pemula', 'author' => 'Andi', 'category_id' => $category->id]);
        $b = Book::create(['title' => 'Belajar Python', 'author' => 'Budi', 'category_id' => $category->id]);
        $c = Book::create(['title' => 'Pemrograman Web', 'author' => 'Citra', 'category_id' => $category->id]);

        $books = $category->books;

        $this->assertCount(3, $books);
        $this->assertTrue($books->contains($a));
        $this->assertTrue($books->contains($b));
        $this->assertTrue($books->contains($c));
    }

    /**
     * Gejala 3: "Nama kategori tidak tampil dengan benar."
     * Penyebab: Blade menampilkan $book->category_id (angka id).
     */
    public function test_daftar_buku_menampilkan_nama_kategori()
    {
        $category = Category::create(['name' => 'Akuntansi']);
        Book::create([
            'title' => 'Pengantar Akuntansi',
            'author' => 'Dewi',
            'category_id' => $category->id,
        ]);

        $response = $this->get('/books');

        $response->assertOk();
        $response->assertSee('Pengantar Akuntansi');
        $response->assertSee('Akuntansi');
        $this->assertStringNotContainsString(
            '>'.$category->id.'<',
            $response->getContent(),
            'Blade masih menampilkan category_id mentah.'
        );
    }

    /**
     * Gejala 4: "Form buku tidak menampilkan pilihan kategori secara benar."
     * Penyebab: <option value> berisi nama kategori, bukan id.
     */
    public function test_form_menampilkan_pilihan_kategori_dengan_value_id()
    {
        $category = Category::create(['name' => 'Manajemen']);

        $response = $this->get('/books/create');

        $response->assertOk();
        $response->assertSee('Manajemen');
        $response->assertSee('value="'.$category->id.'"', false);
        $this->assertStringNotContainsString(
            'value="Manajemen"',
            $response->getContent(),
            'Value option seharusnya id kategori, bukan nama.'
        );
    }

    public function test_buku_dapat_disimpan_dengan_kategori_valid()
    {
        $category = Category::create(['name' => 'Teknologi']);

        $response = $this->post('/books', [
            'title' => 'Laravel untuk Pemula',
            'author' => 'Andi',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel untuk Pemula',
            'category_id' => $category->id,
        ]);
    }

    public function test_validasi_menolak_kategori_yang_tidak_ada()
    {
        $response = $this->post('/books', [
            'title' => 'Buku Uji',
            'author' => 'Andi',
            'category_id' => 999,
        ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseCount('books', 0);
    }

    public function test_validasi_menolak_kategori_kosong()
    {
        $response = $this->post('/books', [
            'title' => 'Buku Uji',
            'author' => 'Andi',
            'category_id' => '',
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    /**
     * Memastikan eager loading benar-benar bekerja: relasi category
     * termuat saat query daftar buku.
     */
    public function test_eager_loading_relasi_category_berjalan()
    {
        $category = Category::create(['name' => 'Teknologi']);
        Book::create(['title' => 'Belajar Python', 'author' => 'Budi', 'category_id' => $category->id]);

        $book = Book::with('category')->first();

        $this->assertTrue($book->relationLoaded('category'));
    }
}
