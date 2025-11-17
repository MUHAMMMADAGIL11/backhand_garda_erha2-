<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'kode_barang',
        'nama_barang',
        'stok',
        'stok_minimum',
    ];

    protected $casts = [
        'stok' => 'integer',
        'stok_minimum' => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}

