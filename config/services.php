<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'pdftotext' => [
        // Ruta al binario pdftotext (poppler-utils). Si está en el PATH, dejar vacío.
        'bin' => env('PDFTOTEXT_BIN', ''),
    ],

    'libreoffice' => [
        // Ruta al binario soffice (LibreOffice) para convertir DOCX → PDF.
        // Windows: 'C:\Program Files\LibreOffice\program\soffice.exe'
        // Linux:   '/usr/bin/soffice' (paquete libreoffice-writer / libreoffice-nogui)
        'bin' => env('LIBREOFFICE_BIN', 'soffice'),
    ],

    'actas' => [
        // Proteger el PDF del acta (solo impresión; sin modificar ni copiar/extraer).
        'proteger_pdf' => env('ACTAS_PROTEGER_PDF', true),
    ],

];
