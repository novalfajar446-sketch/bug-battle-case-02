<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    /**
     * Perbaikan: relasi sebelumnya memakai belongsTo(), padahal satu kategori
     * dapat memiliki BANYAK buku. Relasi yang benar adalah hasMany(),
     * dengan foreign key category_id di tabel books.
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
