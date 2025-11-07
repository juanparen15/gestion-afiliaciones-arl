<?php

use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ErroresImportacionExport;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/descargar-errores-importacion', function () {
    $errores = session('errores_importacion', []);

    if (empty($errores)) {
        abort(404, 'No hay errores para descargar');
    }

    return Excel::download(
        new ErroresImportacionExport($errores),
        'errores_importacion_' . date('Y-m-d_H-i-s') . '.xlsx'
    );
})->middleware('auth')->name('descargar-errores-importacion');
