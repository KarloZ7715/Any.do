import { router } from '@inertiajs/vue3'

/**
 * Composable para gestión de subtareas.
 *
 * Proporciona funciones para CRUD de subtareas usando Inertia.js
 */
export function usarSubtareas() {
	/**
	 * Crear una nueva subtarea.
	 */
	const crearSubtarea = (tareaId, texto) => {
		router.post(
			route('subtareas.store', { tarea: tareaId }),
			{
				texto,
				tarea_id: tareaId,
			},
			{
				preserveScroll: true,
				onError: (errors) => {
					console.error('Error al crear subtarea:', errors)
				},
			},
		)
	}

	/**
	 * Actualizar el texto de una subtarea.
	 */
	const actualizarSubtarea = (tareaId, subtareaId, texto) => {
		router.patch(
			route('subtareas.update', {
				tarea: tareaId,
				subtarea: subtareaId,
			}),
			{
				texto,
				tarea_id: tareaId,
			},
			{
				preserveScroll: true,
				onError: (errors) => {
					console.error('Error al actualizar subtarea:', errors)
				},
			},
		)
	}

	/**
	 * Eliminar una subtarea.
	 */
	const eliminarSubtarea = (tareaId, subtareaId) => {
		if (!confirm('¿Eliminar esta subtarea?')) return

		router.delete(
			route('subtareas.destroy', {
				tarea: tareaId,
				subtarea: subtareaId,
			}),
			{
				preserveScroll: true,
				onError: (errors) => {
					console.error('Error al eliminar subtarea:', errors)
				},
			},
		)
	}

	/**
	 * Alternar el estado de una subtarea (pendiente ↔ completada).
	 */
	const toggleEstado = (tareaId, subtareaId) => {
		router.post(
			route('subtareas.toggle', {
				tarea: tareaId,
				subtarea: subtareaId,
			}),
			{},
			{
				preserveScroll: true,
				onError: (errors) => {
					console.error('Error al cambiar estado:', errors)
				},
			},
		)
	}

	return {
		crearSubtarea,
		actualizarSubtarea,
		eliminarSubtarea,
		toggleEstado,
	}
}
