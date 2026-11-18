<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'author', 'category_id'];

    /**
     * Perbaikan: relasi sebelumnya memakai hasMany(), padahal satu buku
     * hanya memiliki SATU kategori. Relasi yang benar adalah belongsTo(),
     * dengan foreign key category_id di tabel books.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
