<script setup>
import { Flag } from 'lucide-vue-next'

/**
 * Componente reutilizable para mostrar badge de prioridad.
 * Usado en TareaCard, MiCalendario modal, etc.
 */

const props = defineProps({
    /**
     * Nivel de prioridad (1: Alta, 2: Media, 3: Baja)
     */
    prioridad: {
        type: Number,
        required: true,
        validator: (value) => [1, 2, 3].includes(value),
    },
    /**
     * Mostrar icono de bandera
     */
    conIcono: {
        type: Boolean,
        default: true,
    },
    /**
     * Tamaño del componente
     */
    tamanio: {
        type: String,
        default: 'sm',
        validator: (value) => ['xs', 'sm', 'md'].includes(value),
    },
})

/**
 * Mapeo de prioridad a texto
 */
const textosPrioridad = {
    1: 'Alta',
    2: 'Media',
    3: 'Baja',
}

/**
 * Clases de estilo según prioridad
 */
const clasesPrioridad = {
    1: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    2: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
    3: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
}

/**
 * Clases de tamaño
 */
const clasesTamanio = {
    xs: 'text-[10px] px-1.5 py-0.5',
    sm: 'text-xs px-2 py-1',
    md: 'text-sm px-2.5 py-1.5',
}

/**
 * Tamaños de icono
 */
const tamaniosIcono = {
    xs: 10,
    sm: 12,
    md: 14,
}
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-md font-medium"
        :class="[clasesPrioridad[prioridad], clasesTamanio[tamanio]]"
    >
        <Flag v-if="conIcono" :size="tamaniosIcono[tamanio]" />
        {{ textosPrioridad[prioridad] }}
    </span>
</template>
