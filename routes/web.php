<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\SharedAccess\View as SharedAccessView;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/access/{token}', SharedAccessView::class)->name('access.view');
Route::get('/shared/{token}', SharedAccessView::class)->name('shared.view');
