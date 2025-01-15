<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PesananModel;

class DetailPesananModel extends Model
{
    use HasFactory;

    protected $table = 'detail_pesanan';
    
    protected $primaryKey = 'id_detailpesanan';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_pesanan',
        'nama_layanan',
        'harga_layanan',
        'kuantitas',
        'subtotal',
        'deskripsi_servis',
    ];

    /**
     * Define the relationship to the Pesanan model.
     */
    public function pesanan()
    {
        return $this->belongsTo(PesananModel::class, 'id_pesanan', 'id_pesanan');
    }
}
