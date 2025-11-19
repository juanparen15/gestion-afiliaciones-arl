<?php

use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ErroresImportacionExport;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    return view('welcome');
});

// Servir documentación estática de Starlight
Route::get('/docs/{path?}', function ($path = '') {
    $basePath = public_path('docs');

    // Si es la raíz o termina en /, buscar index.html
    if (empty($path) || str_ends_with($path, '/')) {
        $filePath = $basePath . '/' . $path . 'index.html';
    } else {
        // Intentar como directorio con index.html
        $dirPath = $basePath . '/' . $path . '/index.html';
        if (File::exists($dirPath)) {
            $filePath = $dirPath;
        } else {
            // Intentar como archivo directo
            $filePath = $basePath . '/' . $path;
        }
    }

    if (File::exists($filePath)) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response(File::get($filePath), 200)
            ->header('Content-Type', $mimeType);
    }

    abort(404);
})->where('path', '.*');

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
