<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $contadores = [];
        $categorias = [];

        // Solo calcular contadores y categorías si el usuario está autenticado
        if ($request->user()) {
            $usuarioId = $request->user()->id;

            // Contadores de tareas para el sidebar
            $contadores = [
                'tareas_pendientes' => \App\Models\Tarea::where('usuario_id', $usuarioId)
                    ->where('estado', 'pendiente')
                    ->count(),
                'tareas_completadas' => \App\Models\Tarea::where('usuario_id', $usuarioId)
                    ->where('estado', 'completada')
                    ->count(),
                'tareas_hoy' => \App\Models\Tarea::where('usuario_id', $usuarioId)
                    ->where('estado', 'pendiente')
                    ->whereDate('fecha_vencimiento', today())
                    ->count(),
                'tareas_importantes' => \App\Models\Tarea::where('usuario_id', $usuarioId)
                    ->where('estado', 'pendiente')
                    ->where('prioridad', 1)
                    ->count(),
                'tareas_proximos_7_dias' => \App\Models\Tarea::where('usuario_id', $usuarioId)
                    ->where('estado', 'pendiente')
                    ->whereBetween('fecha_vencimiento', [
                        today(),
                        today()->addDays(7)
                    ])
                    ->count(),
                'tareas_totales' => \App\Models\Tarea::where('usuario_id', $usuarioId)
                    ->count(),
            ];

            // Categorías con conteo de tareas para el sidebar
            $categorias = \App\Models\Categoria::where('usuario_id', $usuarioId)
                ->withCount([
                    'tareas' => function ($query) {
                        $query->where('estado', 'pendiente');
                    }
                ])
                ->orderBy('nombre')
                ->get()
                ->map(function ($categoria) {
                    return [
                        'id' => $categoria->id,
                        'nombre' => $categoria->nombre,
                        'color' => $categoria->color,
                        'icono' => $categoria->icono,
                        'tareas_count' => $categoria->tareas_count,
                    ];
                });
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'contadores' => $contadores,
            'categorias' => $categorias,
        ];
    }
}
