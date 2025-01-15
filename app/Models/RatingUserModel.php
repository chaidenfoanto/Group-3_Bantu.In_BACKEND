<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\TukangModel;

class RatingUserModel extends Model
{
    use HasFactory;

    protected $table = 'rating_user'; // Nama tabel di database

    protected $primaryKey = 'id_ratinguser';

    public $incrementing = true;

    protected $keyType = 'integer';

    protected $fillable = [
        'id_user',
        'id_tukang',
        'rating',
        'ulasan',
        'tanggal_rating'
    ];

    public $timestamps = true;

    /**
     * Relasi ke model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Relasi ke model Tukang.
     */
    public function tukang()
    {
        return $this->belongsTo(TukangModel::class, 'id_tukang', 'id_tukang');
    }
}
