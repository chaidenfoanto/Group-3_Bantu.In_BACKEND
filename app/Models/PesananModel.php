<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\TukangModel;
use App\Models\BiayaModel;

class PesananModel extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    /**
     * Menentukan primary key yang digunakan oleh model ini.
     *
     * @var string
     */
    protected $primaryKey = 'id_pesanan';

    /**
     * Menentukan apakah primary key bersifat auto-increment.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Menentukan tipe primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    protected $fillable = [
        'id_user',
        'id_tukang',
        'id_biaya',
        'waktu_pesan',
        'waktu_servis',
        'alamat_servis',
        'metode_pembayaran',
    ];

    /**
     * Menentukan relasi dengan model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Menentukan relasi dengan model Tukang.
     */
    public function tukang()
    {
        return $this->belongsTo(TukangModel::class, 'id_tukang', 'id_tukang');
    }

    /**
     * Menentukan relasi dengan model Biaya.
     */
    public function biaya()
    {
        return $this->belongsTo(BiayaModel::class, 'id_biaya', 'id_biaya');
    }
}
