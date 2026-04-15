<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

Route::prefix('tasks')->group(function () {
    Route::get('/table', [TaskController::class, 'table'])->name('tasks.table');
    Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
});
