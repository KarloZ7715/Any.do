<script setup>
import { X } from 'lucide-vue-next'
import CheckboxRedondo from '@/Components/CheckboxRedondo.vue'

/**
 * Componente reutilizable para item de tarea simple.
 * Muestra: checkbox + título + botón eliminar (para completadas)
 *
 * Usado en TodasLasTareas.vue y otras vistas de lista
 */

const props = defineProps({
    /**
     * Objeto tarea
     */
    tarea: {
        type: Object,
        required: true,
    },
    /**
     * Si está seleccionada visualmente
     */
    seleccionada: {
        type: Boolean,
        default: false,
    },
    /**
     * Índice para animación escalonada
     */
    indice: {
        type: Number,
        default: 0,
    },
})

const emit = defineEmits(['seleccionar', 'toggle', 'eliminar'])

const esCompletada = () => props.tarea.estado === 'completada'
</script>

<template>
    <div
        @click="emit('seleccionar', tarea)"
        class="group/tarea flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all duration-200 animate-slide-in"
        :style="{ animationDelay: `${indice * 30}ms` }"
        :class="[
            seleccionada
                ? 'bg-primary/10 shadow-[0_2px_8px_0px_rgba(0,0,0,0.06)] dark:shadow-none'
                : 'hover:bg-accent'
        ]"
    >
        <!-- Checkbox -->
        <div class="shrink-0">
            <CheckboxRedondo
                :checked="esCompletada()"
                @update:checked="emit('toggle', tarea)"
                @click.stop
            />
        </div>

        <!-- Título de la tarea -->
        <div class="flex-1 min-w-0">
            <p
                class="text-sm font-medium transition-all duration-300"
                :class="[
                    esCompletada()
                        ? 'line-through text-muted-foreground opacity-60'
                        : 'text-foreground'
                ]"
            >
                {{ tarea.titulo }}
            </p>
        </div>

        <!-- Botón eliminar (solo para completadas) -->
        <button
            v-if="esCompletada()"
            @click.stop="emit('eliminar', tarea)"
            class="shrink-0 opacity-0 group-hover/tarea:opacity-100 p-1.5 rounded-lg hover:bg-destructive/10 text-destructive transition-all duration-200"
        >
            <X :size="16" />
        </button>
    </div>
</template>
