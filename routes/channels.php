<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tukang.{id_tukang}', function ($user, $id_tukang) {
    return true; 
});

Broadcast::channel('servicetime.{id_pesanan}', function ($user, $id_pesanan) {
    // Periksa apakah user memiliki akses untuk pesanan ini
    return true; // Sesuaikan dengan logika otorisasi Anda
});
