<script setup>
import { Check, X, ListChecks } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    tarea: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['editar'])

// Verificar si tiene subtareas
const tieneSubtareas = computed(() => {
    return props.tarea.subtareas && props.tarea.subtareas.length > 0
})

const estaCompletada = computed(() => {
    return props.tarea.estado === 'completada'
})

// Toggle estado completada
const toggleCompletada = () => {
    router.patch(
        route('tareas.toggle', props.tarea.id),
        {},
        {
            preserveScroll: true,
        },
    )
}

// Eliminar tarea
const eliminarTarea = () => {
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        router.delete(route('tareas.destroy', props.tarea.id), {
            preserveScroll: true,
        })
    }
}

// Abrir modal de edición
const abrirModal = () => {
    emit('editar', props.tarea)
}

// Color de prioridad
const colorPrioridad = computed(() => {
    const colores = {
        1: '#EF4444', // Rojo - Alta
        2: '#EAB308', // Amarillo - Media
        3: '#22C55E', // Verde - Baja
    }
    return colores[props.tarea.prioridad] || colores[2]
})
</script>

<template>
    <div :class="[
        'group relative rounded-lg border',
        'transition-[background-color,border-color] duration-200',
        estaCompletada
            ? 'bg-muted/50 border-border'
            : 'bg-task-card border-border hover:bg-[#393939] dark:hover:bg-[#393939]',
    ]">
        <!-- Contenido principal -->
        <div class="p-3 flex items-start gap-3">
            <!-- Checkbox circular -->
            <button @click.stop="toggleCompletada" :class="[
                'shrink-0 w-5 h-5 rounded-full border-2 transition-all duration-200 flex items-center justify-center',
                estaCompletada
                    ? 'bg-muted-foreground/30 dark:bg-muted-foreground/30 border-muted-foreground/30 dark:border-muted-foreground/30'
                    : 'border-(--task-checkbox-border) hover:border-sidebar-primary dark:hover:border-sidebar-primary',
            ]">
                <Check v-if="estaCompletada" :size="14" class="text-white" :stroke-width="3" />
            </button>

            <!-- Contenido de la tarea -->
            <div class="flex-1 min-w-0 cursor-pointer" @click="abrirModal">
                <!-- Categoría -->
                <p v-if="tarea.categoria" class="text-xs text-muted-foreground dark:text-muted-foreground mb-1">
                    {{ tarea.categoria.nombre }}
                </p>

                <!-- Título de la tarea -->
                <h3 :class="[
                    'text-sm font-medium transition-all duration-200',
                    estaCompletada
                        ? 'line-through text-muted-foreground dark:text-muted-foreground'
                        : 'text-foreground dark:text-foreground',
                ]">
                    {{ tarea.titulo }}
                </h3>

                <!-- Iconos adicionales -->
                <div class="flex items-center gap-2 mt-2">
                    <!-- Indicador de prioridad (cilíndrico) -->
                    <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: colorPrioridad }"
                        :title="tarea.prioridad === 1 ? 'Alta' : tarea.prioridad === 2 ? 'Media' : 'Baja'" />

                    <!-- Icono de subtareas -->
                    <div v-if="tieneSubtareas"
                        class="flex items-center gap-1 text-xs text-muted-foreground dark:text-muted-foreground">
                        <ListChecks :size="14" />
                        <span>{{ tarea.subtareas.length }}</span>
                    </div>
                </div>
            </div>

            <!-- Botón eliminar (solo visible si está completada) -->
            <button v-if="estaCompletada" @click.stop="eliminarTarea"
                class="shrink-0 relative w-5 h-5 flex items-center justify-center rounded-full border border-(--task-checkbox-border) group-hover:border-primary/50 transition-colors duration-200 mt-0.5">
                <X :size="14" />
            </button>
        </div>
    </div>
</template>
```
