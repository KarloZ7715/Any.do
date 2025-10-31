import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable para gestión de subtareas con actualizaciones optimistas.
 *
 * Proporciona funciones para CRUD de subtareas usando Inertia.js
 * con actualizaciones optimistas (UI primero, backend después).
 */
export function usarSubtareas() {
	const page = usePage()

	/**
	 * Actualizar subtareas en el estado local (optimistic update).
	 */
	const actualizarSubtareasLocales = (tareaId, actualizador) => {
		// Buscar la tarea en las props actuales
		const tareas = page.props.tareas
		if (!tareas) return

		// Si es array simple (TodasLasTareas)
		if (Array.isArray(tareas)) {
			const tarea = tareas.find((t) => t.id === tareaId)
			if (tarea && tarea.subtareas) {
				actualizador(tarea.subtareas)
			}
		}
	}

	/**
	 * Crear una nueva subtarea (optimistic).
	 */
	const crearSubtarea = (tareaId, texto) => {
		// 1. Actualización optimista en UI
		actualizarSubtareasLocales(tareaId, (subtareas) => {
			// Crear subtarea temporal con ID negativo
			const subtareaTemporal = {
				id: -Date.now(), // ID temporal negativo
				tarea_id: tareaId,
				texto,
				completada: false,
				created_at: new Date().toISOString(),
				updated_at: new Date().toISOString(),
			}
			subtareas.push(subtareaTemporal)
		})

		// 2. Petición al backend
		router.post(
			route('subtareas.store', { tarea: tareaId }),
			{
				texto,
				tarea_id: tareaId,
			},
			{
				preserveScroll: true,
				preserveState: true, // Mantener el estado optimista hasta que se confirme
				onError: (errors) => {
					console.error('Error al crear subtarea:', errors)
					// El backend revertirá automáticamente al hacer reload
				},
			},
		)
	}

	/**
	 * Actualizar el texto de una subtarea (optimistic).
	 */
	const actualizarSubtarea = (tareaId, subtareaId, texto) => {
		// 1. Actualización optimista en UI
		actualizarSubtareasLocales(tareaId, (subtareas) => {
			const subtarea = subtareas.find((s) => s.id === subtareaId)
			if (subtarea) {
				subtarea.texto = texto
				subtarea.updated_at = new Date().toISOString()
			}
		})

		// 2. Petición al backend
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
				preserveState: true,
				onError: (errors) => {
					console.error('Error al actualizar subtarea:', errors)
				},
			},
		)
	}

	/**
	 * Eliminar una subtarea (optimistic).
	 */
	const eliminarSubtarea = (tareaId, subtareaId) => {
		// 1. Actualización optimista en UI
		actualizarSubtareasLocales(tareaId, (subtareas) => {
			const index = subtareas.findIndex((s) => s.id === subtareaId)
			if (index !== -1) {
				subtareas.splice(index, 1)
			}
		})

		// 2. Petición al backend (solo si el ID es real, no temporal)
		if (subtareaId > 0) {
			router.delete(
				route('subtareas.destroy', {
					tarea: tareaId,
					subtarea: subtareaId,
				}),
				{
					preserveScroll: true,
					preserveState: true,
					onError: (errors) => {
						console.error('Error al eliminar subtarea:', errors)
					},
				},
			)
		}
	}

	/**
	 * Alternar el estado de una subtarea (optimistic).
	 */
	const toggleEstado = (tareaId, subtareaId) => {
		// 1. Actualización optimista en UI
		actualizarSubtareasLocales(tareaId, (subtareas) => {
			const subtarea = subtareas.find((s) => s.id === subtareaId)
			if (subtarea) {
				subtarea.completada = !subtarea.completada
				subtarea.updated_at = new Date().toISOString()
			}
		})

		// 2. Petición al backend
		router.post(
			route('subtareas.toggle', {
				tarea: tareaId,
				subtarea: subtareaId,
			}),
			{},
			{
				preserveScroll: true,
				preserveState: true,
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
