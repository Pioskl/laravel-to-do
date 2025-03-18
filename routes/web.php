<?php

use App\Http\Controllers\ItemController;
use App\Models\Item;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $items = Item::orderBy('created_at', 'desc')->get();
    return Inertia::render('Welcome', ['items' => $items]);
})->name('home');


Route::get('/items', [ItemController::class, 'index'])->name('items.index');

Route::prefix('item')->group(function () {
    Route::post('store', [ItemController::class, 'store'])->name('item.store');
    Route::put('{id}', [ItemController::class, 'update'])->name('item.update');
    Route::delete('{id}', [ItemController::class, 'destroy'])->name('item.destroy');
});

