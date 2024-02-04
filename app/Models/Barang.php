<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    public function pesanan_detail() 
	{
	     return $this->hasMany('App\Models\PesananDetail','barang_id', 'id');
	}

	protected $fillable = [
        'nama_barang',
        'harga',
        'stok',
        'keterangan',
        'gambar',
        'gambar2',
    ];

    protected $casts = [
        'gambar2' => 'array',
    ];
}
