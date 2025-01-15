<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PesananModel;

class HistoryModel extends Model
{
    use HasFactory;

    protected $table = 'history';

    protected $primaryKey = 'id_history';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_pesanan',
        'status',
    ];

    public function pesanan()
    {
        return $this->belongsTo(PesananModel::class, 'id_pesanan', 'id_pesanan');
    }
}
