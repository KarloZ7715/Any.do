<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Vite;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Crear manifest falso para Vite en tests con las entradas necesarias
        $manifestPath = public_path('build/manifest.json');
        $buildDir = dirname($manifestPath);

        if (!file_exists($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        if (!file_exists($manifestPath)) {
            // Manifest con entradas ficticias para evitar errores de Vite en tests
            $fakeManifest = [
                'resources/js/app.js' => [
                    'file' => 'assets/app.js',
                    'src' => 'resources/js/app.js',
                    'isEntry' => true,
                ],
                'resources/js/Pages/Tareas/Index.vue' => [
                    'file' => 'assets/Tareas-Index.js',
                    'src' => 'resources/js/Pages/Tareas/Index.vue',
                    'isEntry' => true,
                ],
            ];

            file_put_contents($manifestPath, json_encode($fakeManifest, JSON_PRETTY_PRINT));
        }
    }

    protected function tearDown(): void
    {
        // Limpiar manifest creado para tests
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            @unlink($manifestPath);
        }

        $buildDir = public_path('build');
        if (file_exists($buildDir) && is_dir($buildDir) && count(scandir($buildDir)) === 2) {
            @rmdir($buildDir);
        }

        parent::tearDown();
    }
}