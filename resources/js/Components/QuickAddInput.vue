<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Folder, Calendar, Flag, ArrowRight } from 'lucide-vue-next'

const emit = defineEmits(['crear-tarea'])

const props = defineProps({
    categorias: {
        type: Array,
        default: () => [],
    },
})

// Estado del input
const texto = ref('')
const inputRef = ref(null)
const mostrarIconos = ref(false)

// Datos parseados del texto
const categoriaSeleccionada = ref(null)
const fechaSeleccionada = ref(null)
const prioridadSeleccionada = ref(null)

// Placeholder inteligente
const placeholder = computed(() => {
    if (texto.value.length > 0) return ''
    return 'Agregar una tarea. Presiona Enter para guardar'
})

// Mostrar flecha cuando hay texto
const mostrarFlecha = computed(() => texto.value.trim().length > 0)

// Parsear texto automáticamente
const parsearTexto = (textoEntrada) => {
    const textoLower = textoEntrada.toLowerCase()

    // Reset valores antes de parsear
    fechaSeleccionada.value = null
    prioridadSeleccionada.value = null

    // Parsear fecha (palabras clave simples)
    if (textoLower.includes('hoy')) {
        fechaSeleccionada.value = new Date()
    } else if (textoLower.includes('mañana') || textoLower.includes('manana')) {
        const manana = new Date()
        manana.setDate(manana.getDate() + 1)
        fechaSeleccionada.value = manana
    }

    // Parsear prioridad
    if (textoLower.includes('urgente') || textoLower.includes('importante')) {
        prioridadSeleccionada.value = 1
    } else if (textoLower.includes('alta')) {
        prioridadSeleccionada.value = 2
    } else if (textoLower.includes('baja')) {
        prioridadSeleccionada.value = 3
    }
}

// Manejar cambio en el input
const manejarCambio = () => {
    mostrarIconos.value = texto.value.length > 0
    parsearTexto(texto.value)
}

// Crear tarea al presionar Enter
const manejarEnter = async () => {
    const textoLimpio = texto.value.trim()
    if (!textoLimpio) return

    // Crear objeto de tarea
    const nuevaTarea = {
        titulo: textoLimpio,
        descripcion: null,
        fecha_vencimiento: fechaSeleccionada.value
            ? fechaSeleccionada.value.toISOString().split('T')[0]
            : null,
        prioridad: prioridadSeleccionada.value || 3,
        categoria_id: categoriaSeleccionada.value,
        estado: 'pendiente',
    }

    // Enviar al backend
    router.post(
        route('tareas.store'),
        nuevaTarea,
        {
            preserveScroll: true,
            onSuccess: () => {
                // Limpiar el form
                texto.value = ''
                categoriaSeleccionada.value = null
                fechaSeleccionada.value = null
                prioridadSeleccionada.value = null
                mostrarIconos.value = false
            },
        }
    )
}

// Auto-focus al montar
onMounted(() => {
    if (inputRef.value) {
        inputRef.value.focus()
    }
})
</script>

<template>
    <div class="w-full max-w-4xl mx-auto px-4 py-6">
        <!-- Quick Add Input Container -->
        <div
            :class="[
                'relative bg-white dark:bg-gray-800 rounded-lg shadow-sm',
                'border-2 transition-all duration-200',
                mostrarIconos
                    ? 'border-indigo-500 dark:border-indigo-400 shadow-md'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
            ]"
        >
            <!-- Input Principal -->
            <div class="flex items-center gap-3 px-4 py-3">
                <input
                    ref="inputRef"
                    v-model="texto"
                    type="text"
                    :placeholder="placeholder"
                    @input="manejarCambio"
                    @keydown.enter="manejarEnter"
                    class="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 text-base"
                />

                <!-- Flecha condicional (solo cuando hay texto) -->
                <transition
                    enter-active-class="transition-all duration-200"
                    leave-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-90"
                    leave-to-class="opacity-0 scale-90"
                >
                    <button
                        v-if="mostrarFlecha"
                        @click="manejarEnter"
                        class="flex-shrink-0 p-2 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white transition-colors duration-200"
                    >
                        <ArrowRight :size="18" />
                    </button>
                </transition>
            </div>

            <!-- Iconos Animados (solo cuando hay texto) -->
            <transition
                enter-active-class="transition-all duration-200 ease-out"
                leave-active-class="transition-all duration-150 ease-in"
                enter-from-class="opacity-0 -translate-y-2"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="mostrarIconos"
                    class="flex items-center gap-2 px-4 pb-3 border-t border-gray-100 dark:border-gray-700 pt-3"
                >
                    <!-- Icono Categoría -->
                    <button
                        @click="() => {}"
                        :class="[
                            'p-2 rounded-md transition-colors duration-150',
                            categoriaSeleccionada
                                ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400',
                        ]"
                        title="Seleccionar categoría"
                    >
                        <Folder :size="18" />
                    </button>

                    <!-- Icono Fecha -->
                    <button
                        @click="() => {}"
                        :class="[
                            'p-2 rounded-md transition-colors duration-150',
                            fechaSeleccionada
                                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400',
                        ]"
                        title="Seleccionar fecha"
                    >
                        <Calendar :size="18" />
                    </button>

                    <!-- Icono Prioridad -->
                    <button
                        @click="() => {}"
                        :class="[
                            'p-2 rounded-md transition-colors duration-150',
                            prioridadSeleccionada
                                ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400',
                        ]"
                        title="Seleccionar prioridad"
                    >
                        <Flag :size="18" />
                    </button>

                    <!-- Texto de ayuda -->
                    <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">
                        Presiona Enter para guardar
                    </span>
                </div>
            </transition>
        </div>

        <!-- Hints de parseo (opcional) -->
        <div v-if="mostrarIconos" class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span v-if="fechaSeleccionada" class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 rounded">
                📅 {{ fechaSeleccionada.toLocaleDateString() }}
            </span>
            <span v-if="prioridadSeleccionada" class="px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded">
                🚩 Prioridad: {{ prioridadSeleccionada === 1 ? 'Alta' : prioridadSeleccionada === 2 ? 'Media' : 'Baja' }}
            </span>
        </div>
    </div>
</template>
