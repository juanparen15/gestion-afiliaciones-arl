<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ColombiaApi
{
    private const BASE = 'https://api-colombia.com/api/v1';
    private const TTL  = 60 * 60 * 24; // 24 horas

    /**
     * Retorna [nombre => nombre] de todos los departamentos, ordenados.
     */
    public static function departamentos(): array
    {
        return Cache::remember('co_api_departamentos', self::TTL, function () {
            try {
                $response = Http::timeout(8)->get(self::BASE . '/Department');
                if ($response->successful()) {
                    return collect($response->json())
                        ->sortBy('name')
                        ->pluck('name', 'name')
                        ->toArray();
                }
            } catch (\Throwable) {
                // API no disponible — devolver vacío para no romper el formulario
            }

            return [];
        });
    }

    /**
     * Retorna [nombre => nombre] de las ciudades del departamento dado.
     */
    public static function ciudades(string $departamento): array
    {
        if (empty($departamento)) {
            return [];
        }

        $cacheKey = 'co_api_ciudades_' . md5($departamento);

        return Cache::remember($cacheKey, self::TTL, function () use ($departamento) {
            try {
                // Primero obtenemos el ID del departamento
                $dptos = Cache::remember('co_api_departamentos_raw', self::TTL, function () {
                    $r = Http::timeout(8)->get(self::BASE . '/Department');
                    return $r->successful() ? $r->json() : [];
                });

                $dpto = collect($dptos)->firstWhere('name', $departamento);
                if (! $dpto) {
                    return [];
                }

                $response = Http::timeout(8)->get(self::BASE . '/Department/' . $dpto['id'] . '/cities');
                if ($response->successful()) {
                    return collect($response->json())
                        ->sortBy('name')
                        ->pluck('name', 'name')
                        ->toArray();
                }
            } catch (\Throwable) {
                // API no disponible
            }

            return [];
        });
    }
}
