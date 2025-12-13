<script setup>
import { Folder } from 'lucide-vue-next'

/**
 * Componente reutilizable para mostrar badge de categoría.
 */

defineProps({
    /**
     * Objeto categoría con nombre y color
     */
    categoria: {
        type: Object,
        required: true,
        validator: (value) => value.nombre && value.color,
    },
    /**
     * Mostrar icono de carpeta
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
        :class="clasesTamanio[tamanio]"
        :style="{
            backgroundColor: categoria.color + '20',
            color: categoria.color,
        }"
    >
        <Folder v-if="conIcono" :size="tamaniosIcono[tamanio]" />
        {{ categoria.nombre }}
    </span>
</template>
