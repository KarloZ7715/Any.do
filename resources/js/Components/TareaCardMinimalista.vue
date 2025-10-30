<script setup>
import { Check, X, ListChecks } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    tarea: {
        type: Object,
        required: true,
    },
})

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
    // TODO: Implementar modal de edición de tarea
    console.log('Abrir modal para editar tarea:', props.tarea.id)
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
    <div
        :class="[
            'group relative rounded-lg border transition-all duration-200',
            estaCompletada
                ? 'bg-gray-100 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700'
                : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-md',
        ]"
    >
        <!-- Contenido principal -->
        <div class="p-3 flex items-start gap-3">
            <!-- Checkbox circular -->
            <button
                @click.stop="toggleCompletada"
                :class="[
                    'flex-shrink-0 w-5 h-5 rounded-full border-2 transition-all duration-200 flex items-center justify-center',
                    estaCompletada
                        ? 'bg-gray-400 dark:bg-gray-600 border-gray-400 dark:border-gray-600'
                        : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500',
                ]"
            >
                <Check
                    v-if="estaCompletada"
                    :size="14"
                    class="text-white"
                    :stroke-width="3"
                />
            </button>

            <!-- Contenido de la tarea -->
            <div class="flex-1 min-w-0 cursor-pointer" @click="abrirModal">
                <!-- Categoría -->
                <p
                    v-if="tarea.categoria"
                    class="text-xs text-gray-500 dark:text-gray-400 mb-1"
                >
                    {{ tarea.categoria.nombre }}
                </p>

                <!-- Título de la tarea -->
                <h3
                    :class="[
                        'text-sm font-medium transition-all duration-200',
                        estaCompletada
                            ? 'line-through text-gray-500 dark:text-gray-500'
                            : 'text-gray-900 dark:text-white',
                    ]"
                >
                    {{ tarea.titulo }}
                </h3>

                <!-- Iconos adicionales -->
                <div class="flex items-center gap-2 mt-2">
                    <!-- Indicador de prioridad (cilíndrico) -->
                    <div
                        class="w-2 h-2 rounded-full"
                        :style="{ backgroundColor: colorPrioridad }"
                        :title="tarea.prioridad === 1 ? 'Alta' : tarea.prioridad === 2 ? 'Media' : 'Baja'"
                    />

                    <!-- Icono de subtareas -->
                    <div
                        v-if="tieneSubtareas"
                        class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"
                    >
                        <ListChecks :size="14" />
                        <span>{{ tarea.subtareas.length }}</span>
                    </div>
                </div>
            </div>

            <!-- Botón eliminar (solo visible si está completada) -->
            <button
                v-if="estaCompletada"
                @click.stop="eliminarTarea"
                class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-200"
            >
                <X :size="14" />
            </button>
        </div>
    </div>
</template>
