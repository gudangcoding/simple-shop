<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'barang_id',
        'pesanan_id',
        'jumlah',
        'jumlah_harga',
    ];

    public function barang()
    {
        return $this->belongsTo('App\Models\Barang', 'barang_id', 'id');
    }

    public function pesanan()
    {
        return $this->belongsTo('App\Models\Pesanan', 'pesanan_id', 'id');
    }
}
