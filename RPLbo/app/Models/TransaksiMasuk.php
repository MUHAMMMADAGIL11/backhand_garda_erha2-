<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiMasuk extends Model
{
    protected $table = 'transaksimasuk';
    protected $primaryKey = 'id_transaksi_masuk';
    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'supplier',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}

