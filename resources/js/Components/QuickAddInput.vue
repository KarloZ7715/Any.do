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
        1: '#EF4444',
        2: '#EAB308',
        3: '#22C55E',
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
    <div class="w-full max-w-4xl mx-auto px-2 py-4 pt-1">
        <!-- Quick Add Input Container -->
        <div :class="[
            'relative bg-card rounded-lg',
            'border transition-all duration-200',
            mostrarIconos
                ? 'border-ring shadow-md hover:border-[#0083ff] focus-within:border-[#0083ff]'
                : 'border-[#868686] hover:border-[#0083ff] focus-within:border-[#0083ff]',
        ]">
            <!-- Input Principal -->
            <div class="flex items-center gap-2 px-3 py-2">
                <input ref="inputRef" v-model="texto" type="text" :placeholder="placeholderTexto" @input="manejarCambio"
                    @keydown.enter="manejarEnter"
                    class="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 text-foreground placeholder:text-muted-foreground text-[13px]" />

                <!-- Flecha condicional (solo cuando hay texto) -->
                <Transition enter-active-class="transition-all duration-200"
                    leave-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-90"
                    leave-to-class="opacity-0 scale-90">
                    <button v-if="mostrarFlecha" @click="manejarEnter"
                        class="shrink-0 p-1.5 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white transition-colors duration-200">
                        <ArrowRight :size="16" />
                    </button>
                </Transition>
            </div>

            <!-- Iconos Animados (solo cuando hay texto) -->
            <Transition enter-active-class="transition-all duration-400 ease-out"
                leave-active-class="transition-all duration-400 ease-in" enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0 -translate-y-1">
                <div v-if="mostrarIconos" class="flex items-center gap-2 px-4 pb-3 border-t border-border pt-3">
                    <!-- Icono/Texto Categoría -->
                    <button v-if="!nombreCategoriaSeleccionada" @click="abrirModalCategoria" :class="[
                        'p-2 rounded-md transition-all duration-200',
                        'hover:bg-blue-100 dark:hover:bg-blue-900/30',
                        'text-muted-foreground hover:text-blue-600 dark:hover:text-blue-400',
                    ]" title="Seleccionar categoría">
                        <Folder :size="18" />
                    </button>
                    <button v-else @click="abrirModalCategoria"
                        class="px-2 py-1 rounded-md text-xs font-medium transition-all duration-200 bg-primary/10 text-primary hover:bg-primary/20">
                        {{ nombreCategoriaSeleccionada }}
                    </button>

                    <!-- Icono/Texto Fecha -->
                    <button v-if="!fechaFormateada" @click="abrirModalFecha" :class="[
                        'p-2 rounded-md transition-all duration-200',
                        'hover:bg-blue-100 dark:hover:bg-blue-900/30',
                        'text-muted-foreground hover:text-blue-600 dark:hover:text-blue-400',
                    ]" title="Seleccionar fecha">
                        <Calendar :size="18" />
                    </button>
                    <button v-else @click="abrirModalFecha"
                        class="px-2 py-1 rounded-md text-xs font-medium transition-all duration-200 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50">
                        {{ fechaFormateada }}
                    </button>

                    <!-- Icono/Círculo Prioridad -->
                    <button v-if="!colorPrioridad" @click="abrirModalPrioridad" :class="[
                        'p-2 rounded-md transition-all duration-200',
                        'hover:bg-red-100 dark:hover:bg-red-900/30',
                        'text-muted-foreground hover:text-red-600 dark:hover:text-red-400',
                    ]" title="Seleccionar prioridad">
                        <Flag :size="18" />
                    </button>
                    <button v-else @click="abrirModalPrioridad"
                        class="p-2 rounded-md transition-all duration-200 hover:bg-accent" title="Cambiar prioridad">
                        <span class="inline-block w-4 h-4 rounded-full" :style="{ backgroundColor: colorPrioridad }" />
                    </button>

                    <!-- Texto de ayuda -->
                    <span class="ml-auto text-xs text-muted-foreground">
                        Presiona Enter
                    </span>
                </div>
            </Transition>
        </div>

        <!-- Modals -->
        <ModalCategoria v-model:open="modalCategoriaAbierto" :categorias="categorias"
            :categoria-seleccionada="categoriaSeleccionada" @seleccionar="seleccionarCategoria" />

        <ModalFecha v-model:open="modalFechaAbierto" :fecha="fechaSeleccionada" :hora="horaSeleccionada"
            @seleccionar="seleccionarFecha" />

        <ModalPrioridad v-model:open="modalPrioridadAbierto" :prioridad-seleccionada="prioridadSeleccionada"
            @seleccionar="seleccionarPrioridad" />
    </div>
</template>
