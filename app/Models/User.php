<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use App\Models\RatingTukangModel;
use App\Models\RatingUserModel;
use App\Models\PesananModel;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $primaryKey = 'id_user';

    public $incrementing = false; 

    protected $keyType = 'string';
    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->id_user)) { // Jika id_user kosong
                $user->id_user = Str::random(20); // Isi dengan string random sepanjang 20 karakter
            }
        });
    }

    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function ratinguser() {
        return $this->hasMany(RatingUserModel::class, 'id_user', 'id_user');
    }

    public function ratingtukang() {
        return $this->hasMany(RatingTukangModel::class, 'id_user', 'id_user');
    }

    public function pesanan() {
        return $this->hasMany(PesananModel::class, 'id_user', 'id_user');
    }
}
