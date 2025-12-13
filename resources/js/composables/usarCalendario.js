import { computed } from 'vue'

/**
 * Composable para lógica de calendario.
 *
 * Centraliza funciones de generación de días, formateo de fechas
 * y verificación de "hoy" usadas en MiCalendario.vue
 *
 * @param {import('vue').Ref<number>} mesRef - Mes actual (1-12)
 * @param {import('vue').Ref<number>} anioRef - Año actual
 * @param {import('vue').Ref<Object>} tareasPorDiaRef - Tareas indexadas por fecha
 * @returns {Object} - Días del mes y utilidades
 */
export function usarCalendario(mesRef, anioRef, tareasPorDiaRef) {

    const nombresMeses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ]


    const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']


    const diasSemanaCompletos = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']

    /**
     * Verifica si una fecha es hoy
     * @param {number} anio
     * @param {number} mes - 0-indexed
     * @param {number} dia
     * @returns {boolean}
     */
    const esHoy = (anio, mes, dia) => {
        const hoy = new Date()
        return (
            hoy.getFullYear() === anio &&
            hoy.getMonth() === mes &&
            hoy.getDate() === dia
        )
    }

    /**
     * Formatea fecha a string YYYY-MM-DD
     * @param {number} anio
     * @param {number} mes - 1-indexed
     * @param {number} dia
     * @returns {string}
     */
    const formatearFecha = (anio, mes, dia) => {
        return `${anio}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`
    }

    /**
     * Formatea fecha a formato: DD-MM-AAAA
     * @param {string} fechaStr
     * @returns {string}
     */
    const formatearFechaLegible = (fechaStr) => {
        const fechaObj = new Date(fechaStr + 'T00:00:00')
        return `${fechaObj.getDate()} de ${nombresMeses[fechaObj.getMonth()]} de ${fechaObj.getFullYear()}`
    }

    /**
     * Genera la estructura de días del mes actual
     */
    const diasDelMes = computed(() => {
        const primerDia = new Date(anioRef.value, mesRef.value - 1, 1)
        const ultimoDia = new Date(anioRef.value, mesRef.value, 0)
        const dias = []

        // Agregar espacios vacíos antes del primer día
        for (let i = 0; i < primerDia.getDay(); i++) {
            dias.push(null)
        }

        // Agregar todos los días del mes
        for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
            const fecha = formatearFecha(anioRef.value, mesRef.value, dia)
            const tareas = tareasPorDiaRef.value[fecha] || []

            dias.push({
                numero: dia,
                fecha,
                tareas,
                cantidadTareas: tareas.length,
                esHoy: esHoy(anioRef.value, mesRef.value - 1, dia),
            })
        }

        return dias
    })

    /**
     * Nombre del mes actual
     */
    const nombreMesActual = computed(() => {
        return nombresMeses[mesRef.value - 1]
    })

    /**
     * Calcula el nuevo mes/año al navegar
     * @param {number} direccion - -1 para anterior, 1 para siguiente
     * @returns {{mes: number, anio: number}}
     */
    const calcularNuevoMes = (direccion) => {
        let nuevoMes = mesRef.value + direccion
        let nuevoAnio = anioRef.value

        if (nuevoMes < 1) {
            nuevoMes = 12
            nuevoAnio--
        } else if (nuevoMes > 12) {
            nuevoMes = 1
            nuevoAnio++
        }

        return { mes: nuevoMes, anio: nuevoAnio }
    }

    return {
        diasDelMes,
        nombreMesActual,
        nombresMeses,
        diasSemana,
        diasSemanaCompletos,
        esHoy,
        formatearFecha,
        formatearFechaLegible,
        calcularNuevoMes,
    }
}
