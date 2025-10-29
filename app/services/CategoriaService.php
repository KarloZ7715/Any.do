<?php

namespace App\Services;

use App\Data\CategoriaData;
use App\Models\Categoria;
use App\Repositories\CategoriaRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CategoriaService
{
    public function __construct(
        private CategoriaRepository $categoriaRepository
    ) {
    }

    /**
     * Obtener todas las categorías del usuario autenticado.
     *
     * @return Collection
     */
    public function obtenerCategoriasUsuario(): Collection
    {
        return $this->categoriaRepository->obtenerPorUsuario(auth()->id());
    }

    /**
     * Crear una nueva categoría.
     *
     * @param CategoriaData $data
     * @return Categoria
     * @throws RuntimeException
     */
    public function crearCategoria(CategoriaData $data): Categoria
    {
        $usuarioId = auth()->id();

        // Validar que el nombre no exista
        if ($this->categoriaRepository->nombreExiste($usuarioId, $data->nombre)) {
            throw new RuntimeException('Ya tienes una categoría con ese nombre');
        }

        $datos = [
            'nombre' => $data->nombre,
            'descripcion' => $data->descripcion,
            'color' => $data->color,
            'icono' => $data->icono,
            'usuario_id' => $usuarioId,
            'es_personal' => false, // Las categorías creadas por usuario nunca son "Personal"
        ];

        return $this->categoriaRepository->crear($datos);
    }

    /**
     * Actualizar una categoría existente.
     *
     * @param Categoria $categoria
     * @param CategoriaData $data
     * @return Categoria
     * @throws RuntimeException
     */
    public function actualizarCategoria(Categoria $categoria, CategoriaData $data): Categoria
    {
        // Validar que el nombre no exista (excluyendo la categoría actual)
        if (
            $this->categoriaRepository->nombreExiste(
                $categoria->usuario_id,
                $data->nombre,
                $categoria->id
            )
        ) {
            throw new RuntimeException('Ya tienes una categoría con ese nombre');
        }

        // Si es categoría Personal, solo permitir editar color e icono
        $datos = [
            'color' => $data->color,
            'icono' => $data->icono,
        ];

        if (!$categoria->es_personal) {
            $datos['nombre'] = $data->nombre;
            $datos['descripcion'] = $data->descripcion;
        }

        $this->categoriaRepository->actualizar($categoria, $datos);

        return $categoria->fresh();
    }

    /**
     * Eliminar una categoría.
     * Si tiene tareas, las mueve a la categoría "Personal".
     *
     * @param Categoria $categoria
     * @return array ['mensaje' => string, 'tareas_movidas' => int]
     * @throws RuntimeException
     */
    public function eliminarCategoria(Categoria $categoria): array
    {
        // Validar que no sea la categoría Personal
        if ($categoria->es_personal) {
            throw new RuntimeException('No se puede eliminar la categoría Personal');
        }

        $usuarioId = $categoria->usuario_id;
        $totalTareas = $this->categoriaRepository->contarTareas($categoria->id);

        // Si la categoría tiene tareas, moverlas a Personal
        if ($totalTareas > 0) {
            $categoriaPersonal = $this->categoriaRepository->obtenerCategoriaPersonal($usuarioId);

            if (!$categoriaPersonal) {
                throw new RuntimeException('No se encontró la categoría Personal del usuario');
            }

            $tareasMov = $this->categoriaRepository->moverTareas(
                $categoria->id,
                $categoriaPersonal->id
            );

            Log::info("Movidas {$tareasMov} tareas de categoría {$categoria->id} a Personal");
        }

        // Eliminar la categoría
        $this->categoriaRepository->eliminar($categoria);

        $mensaje = $totalTareas > 0
            ? "Categoría eliminada. {$totalTareas} tareas movidas a Personal"
            : "Categoría eliminada exitosamente";

        return [
            'mensaje' => $mensaje,
            'tareas_movidas' => $totalTareas,
        ];
    }

    /**
     * Verificar si una categoría puede ser eliminada.
     *
     * @param Categoria $categoria
     * @return array ['puede_eliminar' => bool, 'razon' => string|null, 'tareas_count' => int]
     */
    public function puedeEliminar(Categoria $categoria): array
    {
        if ($categoria->es_personal) {
            return [
                'puede_eliminar' => false,
                'razon' => 'La categoría Personal no se puede eliminar',
                'tareas_count' => 0,
            ];
        }

        $totalTareas = $this->categoriaRepository->contarTareas($categoria->id);

        return [
            'puede_eliminar' => true,
            'razon' => null,
            'tareas_count' => $totalTareas,
        ];
    }
}
