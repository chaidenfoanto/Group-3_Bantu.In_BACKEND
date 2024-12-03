<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\TukangModel;

class LokasiTukangModel extends Model
{
    use HasFactory;

    // Menentukan nama tabel jika berbeda dengan nama model
    protected $table = 'lokasi_tukang';

    // Menentukan primary key
    protected $primaryKey = 'id_lokasi';

    // Primary key menggunakan auto increment
    public $incrementing = true;

    // Tipe primary key
    protected $keyType = 'integer';

    // Kolom yang dapat diisi secara mass assignment
    protected $fillable = [
        'id_tukang',
        'latitude',
        'longitude',
    ];

    public $timestamps = true;

    // Relasi dengan model Tukang
    public function tukang()
    {
        return $this->belongsTo(TukangModel::class, 'id_tukang', 'id_tukang');
    }
}
