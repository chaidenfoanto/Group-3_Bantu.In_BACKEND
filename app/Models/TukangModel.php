<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\LokasiTukangModel;
use App\Models\RatingTukangModel;
use App\Models\RatingUserModel;
use App\Models\PesananModel;
use App\Models\User;

class TukangModel extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $primaryKey = 'id_tukang';

    public $incrementing = false; 

    protected $keyType = 'string';

    protected $table = 'tukang';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'no_hp',
        'spesialisasi',
        'ktp',
        'foto_diri',
        'tukang_location'
    ];

    protected $casts = [
        'tukang_location' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($tukang) {
            if (empty($tukang->id_tukang)) { // Jika id_user kosong
                $tukang->id_tukang = Str::random(20); // Isi dengan string random sepanjang 20 karakter
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratinguser() {
        return $this->hasMany(RatingUserModel::class, 'id_tukang', 'id_tukang');
    }

    public function ratingtukang() {
        return $this->hasMany(RatingTukangModel::class, 'id_tukang', 'id_tukang');
    }

    public function pesanan() {
        return $this->hasMany(PesananModel::class, 'id_tukang', 'id_tukang');
    }

    public function lokasi() {
        return $this->hasMany(LocationModel::class, 'id_tukang', 'id_tukang');
    }
}
