<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_barang',
        'jenis_transaksi',
        'tanggal',
        'jumlah',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function transaksiMasuk()
    {
        return $this->hasOne(TransaksiMasuk::class, 'id_transaksi', 'id_transaksi');
    }

    public function transaksiKeluar()
    {
        return $this->hasOne(TransaksiKeluar::class, 'id_transaksi', 'id_transaksi');
    }
}

