import { computed } from 'vue'

/**
 * Composable para agrupar tareas por fecha (hoy, mañana, próximas, otras).
 *
 * Centraliza la lógica de agrupación usada en TodasLasTareas.vue
 *
 * @param {import('vue').Ref<Array>} tareasRef - Referencia reactiva a las tareas
 * @returns {Object} - Tareas agrupadas y utilidades
 */
export function usarAgruparTareas(tareasRef) {
    /**
     * Obtiene fecha en formato YYYY-MM-DD
     * @param {Date} fecha
     * @returns {string}
     */
    const obtenerFechaFormato = (fecha) => {
        const year = fecha.getFullYear()
        const month = String(fecha.getMonth() + 1).padStart(2, '0')
        const day = String(fecha.getDate()).padStart(2, '0')
        return `${year}-${month}-${day}`
    }

    /**
     * Obtiene la fecha de hoy en formato YYYY-MM-DD
     * @returns {string}
     */
    const obtenerHoyFormato = () => obtenerFechaFormato(new Date())

    /**
     * Obtiene la fecha de mañana en formato YYYY-MM-DD
     * @returns {string}
     */
    const obtenerMananaFormato = () => {
        const manana = new Date()
        manana.setDate(manana.getDate() + 1)
        return obtenerFechaFormato(manana)
    }

    /**
     * Ordena tareas: pendientes primero, completadas después (por fecha de completado)
     * @param {Array} tareas
     * @returns {Array}
     */
    const ordenarTareas = (tareas) => {
        return [...tareas].sort((a, b) => {
            if (a.estado === 'pendiente' && b.estado === 'completada') return -1
            if (a.estado === 'completada' && b.estado === 'pendiente') return 1
            // Ordenar completadas por fecha de completado (últimas primero)
            if (a.estado === 'completada' && b.estado === 'completada') {
                return new Date(b.fecha_completada) - new Date(a.fecha_completada)
            }
            return 0
        })
    }

    /**
     * Tareas agrupadas por distinción temporal
     */
    const tareasAgrupadas = computed(() => {
        const hoyFormato = obtenerHoyFormato()
        const mananaFormato = obtenerMananaFormato()

        const grupos = {
            hoy: [],
            manana: [],
            proximas: [],
            otras: [],
        }

        tareasRef.value.forEach(tarea => {
            if (!tarea.fecha_vencimiento) {
                grupos.otras.push(tarea)
            } else {
                const fechaTareaFormato = tarea.fecha_vencimiento.split(' ')[0]

                if (fechaTareaFormato === hoyFormato) {
                    grupos.hoy.push(tarea)
                } else if (fechaTareaFormato === mananaFormato) {
                    grupos.manana.push(tarea)
                } else if (fechaTareaFormato > mananaFormato) {
                    grupos.proximas.push(tarea)
                } else {
                    grupos.otras.push(tarea)
                }
            }
        })

        // Ordenar cada grupo
        Object.keys(grupos).forEach(key => {
            grupos[key] = ordenarTareas(grupos[key])
        })

        return grupos
    })

    /**
     * Nombres de las distinciones
     */
    const nombresDistinciones = {
        hoy: 'Hoy',
        manana: 'Mañana',
        proximas: 'Próximas',
        otras: 'Otras',
    }

    /**
     * Obtiene la primera tarea disponible en orden de prioridad (hoy > mañana > próximas > otras)
     * @returns {Object|null}
     */
    const obtenerPrimeraTarea = () => {
        const grupos = tareasAgrupadas.value
        return grupos.hoy[0] || grupos.manana[0] || grupos.proximas[0] || grupos.otras[0] || null
    }

    /**
     * Verifica si hay tareas en algún grupo
     * @returns {boolean}
     */
    const hayTareas = computed(() => {
        return tareasRef.value.length > 0
    })

    /**
     * Cuenta total de tareas por grupo
     * @returns {Object}
     */
    const conteosPorGrupo = computed(() => {
        const grupos = tareasAgrupadas.value
        return {
            hoy: grupos.hoy.length,
            manana: grupos.manana.length,
            proximas: grupos.proximas.length,
            otras: grupos.otras.length,
            total: tareasRef.value.length,
        }
    })

    return {
        tareasAgrupadas,
        nombresDistinciones,
        obtenerPrimeraTarea,
        hayTareas,
        conteosPorGrupo,
        // Utilidades exportadas para uso externo
        obtenerFechaFormato,
        obtenerHoyFormato,
        obtenerMananaFormato,
        ordenarTareas,
    }
}
