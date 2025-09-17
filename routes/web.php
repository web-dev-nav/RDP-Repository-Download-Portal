<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DownloadController;

Route::get('/', [DownloadController::class, 'index']);
Route::get('/debug/repositories', [DownloadController::class, 'debugRepositories'])->name('debug.repositories');
Route::post('/download/repository', [DownloadController::class, 'downloadRepository'])->name('download.repository');

// Admin routes for private repository management
Route::get('/admin/private-repos', [DownloadController::class, 'adminIndex'])->name('admin.private-repos');
Route::post('/admin/add-private-repo', [DownloadController::class, 'addPrivateRepo'])->name('admin.add-private-repo');
Route::post('/admin/remove-private-repo', [DownloadController::class, 'removePrivateRepo'])->name('admin.remove-private-repo');
