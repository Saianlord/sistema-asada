<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectSupportController;
use App\Http\Controllers\ProjectEvaluationController;
use App\Http\Controllers\ProjectHistoryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

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
    Route::get('audit-logs', [App\Http\Controllers\GlobalAuditController::class, 'index'])->name('audit.index');
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

    Route::middleware(['role:admin|junta|operations'])->group(function () {
        Route::get('projects/{project}/evaluate', [ProjectEvaluationController::class, 'create'])->name('evaluations.create');
        Route::post('projects/{project}/evaluate', [ProjectEvaluationController::class, 'store'])->name('evaluations.store');
        Route::get('projects/{project}/evaluations/{evaluation}/edit', [ProjectEvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::put('projects/{project}/evaluations/{evaluation}', [ProjectEvaluationController::class, 'update'])->name('evaluations.update');
    });

    Route::middleware(['role:fiscal'])->group(function () {
        Route::get('projects/{project}/history', [ProjectHistoryController::class, 'index'])->name('projects.history.index');
        Route::get('projects/{project}/history/{history}', [ProjectHistoryController::class, 'show'])->name('projects.history.show');
    });

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/evidence', [ProjectSupportController::class, 'downloadEvidence'])->name('projects.evidence.download');
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');

    Route::get('projects/{project}/kanban', [ProjectTaskController::class, 'index'])->name('projects.kanban.index');
    Route::post('projects/{project}/tasks', [ProjectTaskController::class, 'store'])->name('projects.tasks.store');
    Route::get('projects/{project}/tasks/{task}/edit', [ProjectTaskController::class, 'edit'])->name('projects.tasks.edit');
    Route::put('projects/{project}/tasks/{task}', [ProjectTaskController::class, 'update'])->name('projects.tasks.update');
    Route::patch('projects/{project}/tasks/{task}/status', [ProjectTaskController::class, 'updateStatus'])->name('projects.tasks.status.update');

    Route::get('projects/{project}/tracking/create', [ProjectTrackingController::class, 'create'])->name('projects.tracking.create');
    Route::post('projects/{project}/tracking', [ProjectTrackingController::class, 'store'])->name('projects.tracking.store');
    Route::get('projects/{project}/tracking/{tracking}/edit', [ProjectTrackingController::class, 'edit'])->name('projects.tracking.edit');
    Route::put('projects/{project}/tracking/{tracking}', [ProjectTrackingController::class, 'update'])->name('projects.tracking.update');

    Route::post('projects/{project}/documents', [ProjectDocumentController::class, 'store'])->name('projects.documents.store');
    Route::delete('projects/{project}/documents/{document}', [ProjectDocumentController::class, 'destroy'])->name('projects.documents.destroy');

    Route::get('projects/{project}/document-record', [ProjectDocumentRecordController::class, 'index'])->name('projects.document-record.index');
    Route::get('projects/{project}/document-record/{document}/download', [ProjectDocumentRecordController::class, 'download'])->name('projects.document-record.download');

    Route::get('projects/{project}/history', [ProjectHistoryController::class, 'index'])->name('projects.history.index');
});

require __DIR__.'/auth.php';