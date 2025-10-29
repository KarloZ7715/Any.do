<?php

namespace App\Http\Controllers;

use App\Data\CategoriaData;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CategoriaController extends Controller
{
    public function __construct(
        private CategoriaService $categoriaService
    ) {
    }

    /**
     * Mostrar lista de categorías del usuario.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Categoria::class);
        
        $categorias = $this->categoriaService->obtenerCategoriasUsuario();

        return Inertia::render('Categorias/Index', [
            'categorias' => CategoriaResource::collection($categorias),
        ]);
    }

    /**
     * Crear una nueva categoría.
     */
    public function store(CategoriaData $data): RedirectResponse
    {
        $this->authorize('create', Categoria::class);
        
        try {
            $this->categoriaService->crearCategoria($data);

            return back()->with('success', 'Categoría creada exitosamente');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Actualizar una categoría existente.
     */
    public function update(CategoriaData $data, Categoria $categoria): RedirectResponse
    {
        $this->authorize('update', $categoria);
        
        try {
            $this->categoriaService->actualizarCategoria($categoria, $data);

            $mensaje = $categoria->es_personal
                ? 'Categoría Personal actualizada (solo color e icono)'
                : 'Categoría actualizada exitosamente';

            return back()->with('success', $mensaje);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Eliminar una categoría.
     * Si tiene tareas, las mueve a la categoría Personal.
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        $this->authorize('delete', $categoria);
        
        try {
            $resultado = $this->categoriaService->eliminarCategoria($categoria);

            return back()->with('success', $resultado['mensaje']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
