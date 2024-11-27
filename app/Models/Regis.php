<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Regis extends Model
{
    use HasApiTokens, HasFactory;

    protected $table ='regiss';

    protected $fillable = [
        'name', 
        'email', 
        'password'
    ];
}
