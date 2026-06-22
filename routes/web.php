<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectSupportController;
use App\Http\Controllers\ProjectEvaluationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show'])->missing(function () {
        return redirect()->route('users.index')->with('error', 'El usuario no existe.');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:admin|administration'])->group(function () {
        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    });

    Route::middleware(['role:admin|operations'])->group(function () {
        Route::get('projects/{project}/support/edit', [ProjectSupportController::class, 'edit'])->name('projects.support.edit');
        Route::put('projects/{project}/support', [ProjectSupportController::class, 'update'])->name('projects.support.update');
    });

    Route::middleware(['role:admin|junta'])->group(function () {
        Route::get('prioritization', [ProjectController::class, 'prioritization'])->name('projects.prioritization');
        Route::get('projects/{project}/evaluate', [ProjectEvaluationController::class, 'create'])->name('evaluations.create');
        Route::post('projects/{project}/evaluate', [ProjectEvaluationController::class, 'store'])->name('evaluations.store');
        Route::get('projects/{project}/evaluations/{evaluation}/edit', [ProjectEvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::put('projects/{project}/evaluations/{evaluation}', [ProjectEvaluationController::class, 'update'])->name('evaluations.update');
    });

    Route::middleware(['role:admin|administration'])->group(function () {
        Route::get('projects/{project}/approval', [ProjectController::class, 'approvalForm'])->name('projects.approval.create');
        Route::post('projects/{project}/approval', [ProjectController::class, 'storeApproval'])->name('projects.approval.store');
    });

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
});

require __DIR__.'/auth.php';