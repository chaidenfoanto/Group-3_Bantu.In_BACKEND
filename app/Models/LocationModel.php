<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\TukangModel;

class LocationModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_tukang',
        'is_started',
        'is_completed',
        'origin',
        'destination',
        'destination_name',
    ];

    protected $casts = [
        'origin' => 'array',
        'destination' => 'array',
        'is_started' => 'boolean',
        'is_completed' => 'boolean',
    ];

    protected $primaryKey = 'id_lokasi';
    public $incrementing = true;
    protected $keyType = 'integer';
    protected $table = 'location';

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function tukang()
    {
        return $this->belongsTo(TukangModel::class, 'id_tukang', 'id_tukang');
    }
}
