<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\LokasiTukangModel;

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
    ];

    protected static function booted()
    {
        static::creating(function ($tukang) {
            if (empty($tukang->id_tukang)) { // Jika id_user kosong
                $tukang->id_tukang = Str::random(20); // Isi dengan string random sepanjang 20 karakter
            }
        });
    }

    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function lokasi() {
        return $this->hasMany(LokasiTukangModel::class, 'id_tukang', 'id_tukang');
    }
}
