<?php

use App\Http\Controllers\PesananLaundryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/pesanan-laundry')->name('home');

Route::get('/pesanan-laundry', [PesananLaundryController::class, 'index'])->name('pesanan.index');
Route::post('/pesanan-laundry', [PesananLaundryController::class, 'store'])->name('pesanan.store');
Route::get('/pesanan-laundry/{id}/edit', [PesananLaundryController::class, 'edit'])
    ->whereNumber('id')
    ->name('pesanan.edit');
Route::put('/pesanan-laundry/{id}', [PesananLaundryController::class, 'update'])
    ->whereNumber('id')
    ->name('pesanan.update');
Route::delete('/pesanan-laundry/{id}', [PesananLaundryController::class, 'destroy'])
    ->whereNumber('id')
    ->name('pesanan.destroy');
