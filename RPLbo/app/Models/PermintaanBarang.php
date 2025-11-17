<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanBarang extends Model
{
    protected $table = 'permintaanbarang';
    protected $primaryKey = 'id_permintaan';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_barang',
        'jumlah_diminta',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}

