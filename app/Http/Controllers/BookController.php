<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        // Perbaikan: eager loading memakai with('category') (tunggal),
        // sesuai relasi belongsTo() di model Book. Versi awal memakai
        // with('categories') yang tidak ada, sehingga relasi gagal dimuat
        // dan query category dilakukan berulang (N+1 problem).
        $books = Book::with('category')->latest()->get();

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            // Perbaikan: validasi memakai kolom id (sesuai isi option di form),
            // bukan nama kategori.
            'category_id' => 'required|integer|exists:categories,id',
        ], [
            'title.required' => 'Judul wajib diisi.',
            'author.required' => 'Penulis wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
        ]);

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }
}
