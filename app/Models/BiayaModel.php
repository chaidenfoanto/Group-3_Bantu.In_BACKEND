<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PesananModel;

class BiayaModel extends Model
{
    use HasFactory;

    // Nama tabel, jika nama model tidak sesuai dengan nama tabel
    protected $table = 'biaya';

    // Primary key untuk tabel
    protected $primaryKey = 'id_biaya';

    // Primary key tidak auto-incrementing integer
    public $incrementing = true;

    // Tipe data primary key
    protected $keyType = 'integer';

    // Field yang dapat diisi secara mass-assignment
    protected $fillable = [
        'biaya_servis',
        'biaya_total',
    ];

    // Timestamps untuk created_at dan updated_at
    public $timestamps = true;

    // Cast untuk kolom decimal
    protected $casts = [
        'biaya_servis' => 'decimal:2',
        'biaya_total' => 'decimal:2',
    ];

    public function pesanan() {
        return $this->hasOne(PesananModel::class, 'id_biaya', 'id_biaya');
    }
}
