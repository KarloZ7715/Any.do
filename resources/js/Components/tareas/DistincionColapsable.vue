<script setup>
import { ChevronDown, ChevronRight } from 'lucide-vue-next'

/**
 * Componente reutilizable para secciones colapsables de tareas.
 * Usado en TodasLasTareas.vue para las distinciones (Hoy, Mañana, Próximas, Otras)
 */

defineProps({
    /**
     * Título de la distinción
     */
    titulo: {
        type: String,
        required: true,
    },
    /**
     * Cantidad de elementos (para mostrar badge cuando está colapsado)
     */
    cantidad: {
        type: Number,
        default: 0,
    },
    /**
     * Si la sección está expandida
     */
    expandida: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['toggle'])
</script>

<template>
    <div class="mb-4">
        <!-- Header -->
        <button
            @click="emit('toggle')"
            class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-accent transition-colors group"
        >
            <div class="flex items-center gap-2">
                <ChevronDown
                    v-if="expandida"
                    :size="18"
                    class="text-muted-foreground transition-transform"
                />
                <ChevronRight
                    v-else
                    :size="18"
                    class="text-muted-foreground transition-transform"
                />
                <span class="text-sm font-semibold text-foreground">
                    {{ titulo }}
                </span>
            </div>
            <!-- Badge con cantidad cuando está colapsado -->
            <span
                v-if="!expandida && cantidad > 0"
                class="text-xs font-medium text-muted-foreground bg-muted px-2 py-0.5 rounded-full"
            >
                {{ cantidad }}
            </span>
        </button>

        <!-- Contenido (slot) -->
        <div v-if="expandida" class="mt-2 space-y-1">
            <slot />
        </div>
    </div>
</template>
