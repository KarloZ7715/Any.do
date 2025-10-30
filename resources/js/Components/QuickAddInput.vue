<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Folder, Calendar, Flag, ArrowRight } from 'lucide-vue-next'
import ModalCategoria from '@/Components/QuickAdd/ModalCategoria.vue'
import ModalFecha from '@/Components/QuickAdd/ModalFecha.vue'
import ModalPrioridad from '@/Components/QuickAdd/ModalPrioridad.vue'

const props = defineProps({
    categorias: {
        type: Array,
        default: () => [],
    },
    fechaPredeterminada: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Agregar una tarea. Presiona Enter para guardar',
    },
})

// Estado del input
const texto = ref('')
const inputRef = ref(null)
const mostrarIconos = ref(false)

// Estados de modals
const modalCategoriaAbierto = ref(false)
const modalFechaAbierto = ref(false)
const modalPrioridadAbierto = ref(false)

// Datos seleccionados
const categoriaSeleccionada = ref(null)
const fechaSeleccionada = ref(null)
const horaSeleccionada = ref(null)
const prioridadSeleccionada = ref(null)

// Placeholder inteligente
const placeholderTexto = computed(() => {
    if (texto.value.length > 0) return ''
    return props.placeholder
})

// Mostrar flecha cuando hay texto
const mostrarFlecha = computed(() => texto.value.trim().length > 0)

// Obtener nombre de categoría seleccionada
const nombreCategoriaSeleccionada = computed(() => {
    if (!categoriaSeleccionada.value) return null
    const categoria = props.categorias.find(c => c.id === categoriaSeleccionada.value)
    return categoria?.nombre || null
})

// Formatear fecha para mostrar (Ej: "Lun 4:00PM")
const fechaFormateada = computed(() => {
    if (!fechaSeleccionada.value) return null
    
    const fecha = new Date(fechaSeleccionada.value + 'T00:00:00')
    const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
    const diaNombre = dias[fecha.getDay()]
    
    if (!horaSeleccionada.value) {
        return diaNombre
    }
    
    // Formatear hora (Ej: 16:00 -> 4:00PM)
    const [horas, minutos] = horaSeleccionada.value.split(':')
    const horasNum = parseInt(horas)
    const periodo = horasNum >= 12 ? 'PM' : 'AM'
    const horas12 = horasNum % 12 || 12
    
    return `${diaNombre} ${horas12}:${minutos}${periodo}`
})

// Color de prioridad seleccionada
const colorPrioridad = computed(() => {
    if (!prioridadSeleccionada.value) return null
    const colores = {
        1: '#EF4444', // red-500
        2: '#EAB308', // yellow-500
        3: '#22C55E', // green-500
    }
    return colores[prioridadSeleccionada.value]
})

// Manejar cambio en el input
const manejarCambio = () => {
    mostrarIconos.value = texto.value.length > 0
}

// Handlers de modals
const abrirModalCategoria = () => {
    modalCategoriaAbierto.value = true
}

const abrirModalFecha = () => {
    modalFechaAbierto.value = true
}

const abrirModalPrioridad = () => {
    modalPrioridadAbierto.value = true
}

const seleccionarCategoria = (categoriaId) => {
    categoriaSeleccionada.value = categoriaId
}

const seleccionarFecha = ({ fecha, hora }) => {
    fechaSeleccionada.value = fecha
    horaSeleccionada.value = hora
}

const seleccionarPrioridad = (prioridad) => {
    prioridadSeleccionada.value = prioridad
}

// Crear tarea al presionar Enter
const manejarEnter = async () => {
    const textoLimpio = texto.value.trim()
    if (!textoLimpio) return

    // Encontrar categoría Personal como default
    const categoriaPersonal = props.categorias.find(c => c.nombre === 'Personal')
    
    // Crear objeto de tarea con valores por defecto
    const nuevaTarea = {
        titulo: textoLimpio,
        descripcion: null,
        fecha_vencimiento: fechaSeleccionada.value || props.fechaPredeterminada || null,
        prioridad: prioridadSeleccionada.value || 2, // Default: Media (2)
        categoria_id: categoriaSeleccionada.value || categoriaPersonal?.id || null,
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
                horaSeleccionada.value = null
                prioridadSeleccionada.value = null
                mostrarIconos.value = false
            },
        },
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
                    :placeholder="placeholderTexto"
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
                enter-active-class="transition-all duration-300 ease-out"
                leave-active-class="transition-all duration-200 ease-in"
                enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div
                    v-if="mostrarIconos"
                    class="flex items-center gap-2 px-4 pb-3 border-t border-gray-100 dark:border-gray-700 pt-3"
                >
                    <!-- Icono/Texto Categoría -->
                    <button
                        v-if="!nombreCategoriaSeleccionada"
                        @click="abrirModalCategoria"
                        :class="[
                            'p-2 rounded-md transition-all duration-200',
                            'hover:bg-indigo-100 dark:hover:bg-indigo-900/30',
                            'text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400',
                        ]"
                        title="Seleccionar categoría"
                    >
                        <Folder :size="18" />
                    </button>
                    <button
                        v-else
                        @click="abrirModalCategoria"
                        class="px-2 py-1 rounded-md text-xs font-medium transition-all duration-200 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-200 dark:hover:bg-indigo-900/50"
                    >
                        {{ nombreCategoriaSeleccionada }}
                    </button>

                    <!-- Icono/Texto Fecha -->
                    <button
                        v-if="!fechaFormateada"
                        @click="abrirModalFecha"
                        :class="[
                            'p-2 rounded-md transition-all duration-200',
                            'hover:bg-blue-100 dark:hover:bg-blue-900/30',
                            'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400',
                        ]"
                        title="Seleccionar fecha"
                    >
                        <Calendar :size="18" />
                    </button>
                    <button
                        v-else
                        @click="abrirModalFecha"
                        class="px-2 py-1 rounded-md text-xs font-medium transition-all duration-200 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50"
                    >
                        {{ fechaFormateada }}
                    </button>

                    <!-- Icono/Círculo Prioridad -->
                    <button
                        v-if="!colorPrioridad"
                        @click="abrirModalPrioridad"
                        :class="[
                            'p-2 rounded-md transition-all duration-200',
                            'hover:bg-red-100 dark:hover:bg-red-900/30',
                            'text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400',
                        ]"
                        title="Seleccionar prioridad"
                    >
                        <Flag :size="18" />
                    </button>
                    <button
                        v-else
                        @click="abrirModalPrioridad"
                        class="p-2 rounded-md transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                        title="Cambiar prioridad"
                    >
                        <span
                            class="inline-block w-4 h-4 rounded-full"
                            :style="{ backgroundColor: colorPrioridad }"
                        />
                    </button>

                    <!-- Texto de ayuda -->
                    <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">
                        Presiona Enter para guardar
                    </span>
                </div>
            </transition>
        </div>

        <!-- Modals -->
        <ModalCategoria
            v-model:open="modalCategoriaAbierto"
            :categorias="categorias"
            :categoria-seleccionada="categoriaSeleccionada"
            @seleccionar="seleccionarCategoria"
        />

        <ModalFecha
            v-model:open="modalFechaAbierto"
            :fecha="fechaSeleccionada"
            :hora="horaSeleccionada"
            @seleccionar="seleccionarFecha"
        />

        <ModalPrioridad
            v-model:open="modalPrioridadAbierto"
            :prioridad-seleccionada="prioridadSeleccionada"
            @seleccionar="seleccionarPrioridad"
        />
    </div>
</template>
