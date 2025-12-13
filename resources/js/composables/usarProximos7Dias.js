import { ref, watch } from 'vue'

/**
 * Composable para generar estructura de 7 días.
 *
 * Centraliza la lógica de generación de días usada en Proximos7Dias.vue
 *
 * @param {import('vue').Ref<Object>} tareasPorFechaRef - Tareas indexadas por fecha
 * @returns {Object} - Estructura de días y utilidades
 */
export function usarProximos7Dias(tareasPorFechaRef) {

    const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']

    const diasSemanaCortos = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

    /**
     * Estado local para los días
     */
    const sieteDias = ref([])

    /**
     * Genera la estructura de los próximos 7 días
     * @returns {Array}
     */
    const generarSieteDias = () => {
        const dias = []
        const hoy = new Date()
        hoy.setHours(0, 0, 0, 0)

        for (let i = 0; i < 7; i++) {
            const fecha = new Date(hoy)
            fecha.setDate(hoy.getDate() + i)

            const fechaStr = fecha.toISOString().split('T')[0]
            const tareas = tareasPorFechaRef.value[fechaStr] || []

            // Separar tareas pendientes y completadas
            const tareasPendientes = tareas.filter(t => t.estado === 'pendiente')
            const tareasCompletadas = tareas.filter(t => t.estado === 'completada')

            // Ordenar completadas por fecha de completado
            tareasCompletadas.sort((a, b) => new Date(b.fecha_completada) - new Date(a.fecha_completada))

            let etiqueta = ''
            let subEtiqueta = ''

            if (i === 0) {
                etiqueta = 'Hoy'
                subEtiqueta = diasSemanaCortos[fecha.getDay()]
            } else if (i === 1) {
                etiqueta = 'Mañana'
                subEtiqueta = diasSemanaCortos[fecha.getDay()]
            } else {
                etiqueta = diasSemana[fecha.getDay()]
                subEtiqueta = ''
            }

            dias.push({
                fecha: fechaStr,
                fechaObj: fecha,
                etiqueta,
                subEtiqueta,
                todasLasTareas: [...tareasPendientes, ...tareasCompletadas],
                tareasPendientes,
            })
        }

        return dias
    }

    // Sincronizar estado local cuando cambian las props
    watch(tareasPorFechaRef, () => {
        sieteDias.value = generarSieteDias()
    }, { immediate: true, deep: true })

    /**
     * Formatea fecha para display
     * @param {Date} fecha
     * @returns {string}
     */
    const formatearFechaCorta = (fecha) => {
        return `${fecha.getDate()}/${fecha.getMonth() + 1}`
    }

    return {
        sieteDias,
        diasSemana,
        diasSemanaCortos,
        generarSieteDias,
        formatearFechaCorta,
    }
}
