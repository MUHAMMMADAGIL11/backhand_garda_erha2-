<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKeluar extends Model
{
    protected $table = 'transaksikeluar';
    protected $primaryKey = 'id_transaksi_keluar';
    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'tujuan',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}

