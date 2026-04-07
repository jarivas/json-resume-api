<?php

use App\Http\Controllers\Import\CertificateImportController;
use App\Http\Controllers\Import\EducationImportController;
use App\Http\Controllers\Import\ImportJson;
use App\Http\Controllers\Import\ImportResume;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('import')
    ->group(function () {
        Route::post('json', ImportJson::class);
        Route::post('resume', ImportResume::class);
        Route::post('education', EducationImportController::class);
        Route::post('certificate', CertificateImportController::class);
    });
