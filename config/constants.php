<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Salario Mínimo Legal Vigente
    |--------------------------------------------------------------------------
    |
    | Salario mínimo mensual legal vigente en Colombia.
    | Debe ser actualizado cada año según el decreto gubernamental.
    |
    | Año 2026: $1.750.905 COP (Decreto 1572 y 1573)
    |
    */
    'salario_minimo_legal' => env('SALARIO_MINIMO_LEGAL', 1750905),

    /*
    |--------------------------------------------------------------------------
    | Auxilio de Transporte
    |--------------------------------------------------------------------------
    |
    | Auxilio de transporte legal vigente en Colombia.
    | Se suma al salario mínimo para empleados que lo requieran.
    |
    | Año 2026: $249.095 COP
    |
    */
    'auxilio_transporte' => env('AUXILIO_TRANSPORTE', 249095),
];
