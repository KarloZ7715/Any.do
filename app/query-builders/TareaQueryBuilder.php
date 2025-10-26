<?php

namespace App\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * Query Builder personalizado para Tareas.
 * 
 * Utiliza Spatie Query Builder para facilitar filtros, ordenamiento
 * y búsqueda desde la API.
 */
class TareaQueryBuilder extends QueryBuilder
{
    /**
     * Crear una instancia del query builder.
     */
    public static function for($subject, $request = null): static
    {
        return parent::for($subject, $request)
            ->allowedFilters([
                AllowedFilter::exact('usuario_id'),
                AllowedFilter::exact('categoria_id'),
                AllowedFilter::exact('estado'),
                AllowedFilter::exact('prioridad'),
                AllowedFilter::scope('vencidas'),
                AllowedFilter::scope('pendientes'),
                AllowedFilter::scope('completadas'),
                'titulo',
                'descripcion',
            ])
            ->allowedSorts([
                'titulo',
                'prioridad',
                'fecha_vencimiento',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'usuario',
                'categoria',
                'comentarios',
                'comentarios.usuario',
            ])
            ->defaultSort('-created_at');
    }

    /**
     * Scope para filtrar por rango de fechas.
     */
    public function whereEntreFechas(?string $desde = null, ?string $hasta = null): self
    {
        if ($desde) {
            $this->where('fecha_vencimiento', '>=', $desde);
        }

        if ($hasta) {
            $this->where('fecha_vencimiento', '<=', $hasta);
        }

        return $this;
    }

    /**
     * Scope para filtrar tareas del día actual.
     */
    public function whereHoy(): self
    {
        return $this->whereDate('fecha_vencimiento', today());
    }

    /**
     * Scope para filtrar tareas de esta semana.
     */
    public function whereSemana(): self
    {
        return $this->whereBetween('fecha_vencimiento', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope para filtrar tareas de este mes.
     */
    public function whereMes(): self
    {
        return $this->whereBetween('fecha_vencimiento', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }
}
