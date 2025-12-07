<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { X, Calendar, Flag, Folder, Trash2 } from 'lucide-vue-next'
import { Dialog, DialogContent, DialogTitle, DialogDescription } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Textarea } from '@/Components/ui/textarea'
import { VisuallyHidden } from 'radix-vue'
import ModalCategoria from '@/Components/QuickAdd/ModalCategoria.vue'
import ModalFecha from '@/Components/QuickAdd/ModalFecha.vue'
import ModalPrioridad from '@/Components/QuickAdd/ModalPrioridad.vue'
import ListaSubtareas from '@/Components/Subtareas/ListaSubtareas.vue'
import { usarSubtareas } from '@/composables/usarSubtareas'

const props = defineProps({
    tarea: {
        type: Object,
        default: null,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    open: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:open', 'cerrar'])

// Composable de subtareas
const { crearSubtarea, actualizarSubtarea, eliminarSubtarea, toggleEstado } = usarSubtareas()

// Estados de modals para iconos
const modalCategoriaAbierto = ref(false)
const modalFechaAbierto = ref(false)
const modalPrioridadAbierto = ref(false)

// Estado del formulario
const form = ref({
    titulo: '',
    descripcion: '',
    categoria_id: null,
    prioridad: 2,
    fecha_vencimiento: null,
    hora_vencimiento: null,
})

// Estado local para subtareas (reactivo)
const subtareasLocales = ref([])

// Cargar datos de la tarea si existe (modo edición)
watch(() => props.tarea, (tarea) => {
    if (tarea) {
        // Separar fecha y hora si viene como datetime
        let fecha = null
        let hora = null

        if (tarea.fecha_vencimiento) {
            const datetime = tarea.fecha_vencimiento

            // Si es string ISO (2025-10-31T00:00:00.000000Z o 2025-10-31T14:30:00.000000Z)
            if (typeof datetime === 'string') {
                try {
                    // Parsear como Date para manejar timezone correctamente
                    const dateObj = new Date(datetime)

                    if (!isNaN(dateObj.getTime())) {
                        // Extraer fecha en formato YYYY-MM-DD (local)
                        const year = dateObj.getFullYear()
                        const month = String(dateObj.getMonth() + 1).padStart(2, '0')
                        const day = String(dateObj.getDate()).padStart(2, '0')
                        fecha = `${year}-${month}-${day}`

                        // Extraer hora en formato HH:MM (local)
                        const hours = String(dateObj.getHours()).padStart(2, '0')
                        const minutes = String(dateObj.getMinutes()).padStart(2, '0')

                        // Solo guardar hora si no es 00:00
                        if (hours !== '00' || minutes !== '00') {
                            hora = `${hours}:${minutes}`
                        }
                    }
                } catch (error) {
                    console.error('Error parseando fecha:', error)
                }
            }
        }

        form.value = {
            titulo: tarea.titulo || '',
            descripcion: tarea.descripcion || '',
            categoria_id: tarea.categoria?.id || null,
            prioridad: tarea.prioridad || 2,
            fecha_vencimiento: fecha,
            hora_vencimiento: hora,
        }

        // Cargar subtareas locales
        subtareasLocales.value = [...(tarea.subtareas?.data || tarea.subtareas || [])]
    }
}, { immediate: true })

// Computeds
const esEdicion = computed(() => !!props.tarea)

const nombreCategoriaSeleccionada = computed(() => {
    if (!form.value.categoria_id) return null
    const categoria = props.categorias.find(c => c.id === form.value.categoria_id)
    return categoria?.nombre || null
})

const fechaFormateada = computed(() => {
    if (!form.value.fecha_vencimiento) return null

    try {
        const fecha = new Date(form.value.fecha_vencimiento + 'T00:00:00')

        // Verificar que la fecha es válida
        if (isNaN(fecha.getTime())) return null

        const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']

        let resultado = `${dias[fecha.getDay()]} ${fecha.getDate()} ${meses[fecha.getMonth()]}`

        // Agregar hora si está disponible
        if (form.value.hora_vencimiento) {
            resultado += ` ${form.value.hora_vencimiento}`
        }

        return resultado
    } catch (error) {
        return null
    }
})

const colorPrioridad = computed(() => {
    const colores = {
        1: '#EF4444',
        2: '#EAB308',
        3: '#22C55E',
    }
    return colores[form.value.prioridad]
})

const nombrePrioridad = computed(() => {
    const nombres = { 1: 'Alta', 2: 'Media', 3: 'Baja' }
    return nombres[form.value.prioridad]
})

// Handlers para modales
const seleccionarCategoria = (categoriaId) => {
    form.value.categoria_id = categoriaId
}

const seleccionarFecha = ({ fecha, hora }) => {
    form.value.fecha_vencimiento = fecha
    form.value.hora_vencimiento = hora
}

const seleccionarPrioridad = (prioridad) => {
    form.value.prioridad = prioridad
}

// Guardar cambios
const guardar = () => {
    if (!form.value.titulo.trim()) return

    const payload = {
        titulo: form.value.titulo,
        descripcion: form.value.descripcion,
        categoria_id: form.value.categoria_id,
        prioridad: form.value.prioridad,
        fecha_vencimiento: form.value.fecha_vencimiento,
        hora_vencimiento: form.value.hora_vencimiento,
        estado: props.tarea?.estado || 'pendiente',
    }

    router.patch(
        route('tareas.update', props.tarea.id),
        payload,
        {
            preserveScroll: true,
            onSuccess: () => {
                cerrarModal()
            },
        },
    )
}

// Handlers de subtareas con estado local reactivo
const handleCrearSubtarea = (texto) => {
    if (props.tarea) {
        // Crear optimísticamente en el estado local
        const nuevaSubtarea = {
            id: Date.now(), // ID temporal
            texto: texto,
            estado: 'pendiente',
            tarea_id: props.tarea.id,
        }

        // Agregar al estado local inmediatamente
        subtareasLocales.value.push(nuevaSubtarea)

        // Luego hacer la petición al servidor
        crearSubtarea(props.tarea.id, texto)
    }
}

const handleActualizarSubtarea = (subtarea, nuevoTexto) => {
    if (props.tarea) {
        // Actualización optimista en el estado local
        const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)

        if (index !== -1) {
            subtareasLocales.value[index] = { ...subtareasLocales.value[index], texto: nuevoTexto }
        }

        actualizarSubtarea(props.tarea.id, subtarea.id, nuevoTexto)
    }
}

const handleEliminarSubtarea = (subtarea) => {
    if (props.tarea) {
        // Actualización optimista en el estado local
        const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)

        if (index !== -1) {
            subtareasLocales.value.splice(index, 1)
        }

        eliminarSubtarea(props.tarea.id, subtarea.id)
    }
}

const handleToggleSubtarea = (subtarea) => {
    if (props.tarea) {
        // Actualización optimista en el estado local
        const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)

        if (index !== -1) {
            const nuevoEstado = subtareasLocales.value[index].estado === 'pendiente' ? 'completada' : 'pendiente'
            subtareasLocales.value[index] = { ...subtareasLocales.value[index], estado: nuevoEstado }
        }

        toggleEstado(props.tarea.id, subtarea.id)
    }
}

// Eliminar tarea
const eliminar = () => {
    if (!confirm('¿Estás seguro de que deseas eliminar esta tarea?')) return

    router.delete(route('tareas.destroy', props.tarea.id), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModal()
        },
    })
}

// Cerrar modal
const cerrarModal = () => {
    emit('update:open', false)
    emit('cerrar')
}
</script>

<template>
    <Dialog :open="open" @update:open="val => emit('update:open', val)">
        <DialogContent class="sm:max-w-[550px] max-h-[700px] p-0 overflow-hidden">
            <!-- Títulos ocultos para accesibilidad -->
            <VisuallyHidden>
                <DialogTitle>Editar Tarea</DialogTitle>
                <DialogDescription>Edita los detalles de tu tarea</DialogDescription>
            </VisuallyHidden>

            <!-- Header con título editable -->
            <div class="px-6 pt-6 pb-4 border-b border-border">
                <!-- Título como input invisible -->
                <input v-model="form.titulo" type="text" placeholder="Título de la tarea"
                    class="w-full text-2xl font-semibold bg-transparent border-none outline-none focus:ring-0 p-0 text-foreground placeholder:text-muted-foreground"
                    @keydown.enter.prevent="guardar" />

                <!-- Iconos de metadatos (Categoría, Fecha, Prioridad) -->
                <div class="flex items-center gap-2 mt-4">
                    <!-- Categoría -->
                    <button type="button" @click="modalCategoriaAbierto = true"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                        :class="nombreCategoriaSeleccionada ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'">
                        <Folder :size="14" />
                        <span>{{ nombreCategoriaSeleccionada || 'Categoría' }}</span>
                    </button>

                    <!-- Fecha -->
                    <button type="button" @click="modalFechaAbierto = true"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                        :class="form.fecha_vencimiento ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'">
                        <Calendar :size="14" />
                        <span>{{ fechaFormateada || 'Fecha' }}</span>
                    </button>

                    <!-- Prioridad -->
                    <button type="button" @click="modalPrioridadAbierto = true"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors bg-accent hover:bg-accent/80">
                        <Flag :size="14" :style="{ color: colorPrioridad }" />
                        <span :style="{ color: colorPrioridad }">
                            {{ nombrePrioridad }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Contenido con scroll -->
            <div class="overflow-y-auto px-6 py-4">
                <!-- Descripción -->
                <div class="pb-4">
                    <Textarea v-model="form.descripcion" placeholder="Agregar descripción..."
                        class="min-h-[80px] resize-y text-sm" />
                </div>

                <!-- Subtareas -->
                <div>
                    <ListaSubtareas :tarea-id="tarea?.id" :subtareas="subtareasLocales" modo="edit"
                        @crear="handleCrearSubtarea" @actualizar="handleActualizarSubtarea"
                        @eliminar="handleEliminarSubtarea" @toggle="handleToggleSubtarea" />
                </div>
            </div>

            <!-- Footer con botones -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-border">
                <Button type="button" variant="ghost" size="sm" @click="eliminar"
                    class="text-destructive hover:text-destructive/90 hover:bg-destructive/10">
                    <Trash2 :size="16" class="mr-2" />
                    Eliminar
                </Button>

                <Button type="button" @click="guardar" size="sm">
                    Guardar
                </Button>
            </div>

            <!-- Modals para iconos (reutilizando los del QuickAddInput) -->
            <ModalCategoria v-model:open="modalCategoriaAbierto" :categorias="categorias"
                :categoria-seleccionada="form.categoria_id" @seleccionar="seleccionarCategoria" />

            <ModalFecha v-model:open="modalFechaAbierto" :fecha="form.fecha_vencimiento" :hora="form.hora_vencimiento"
                @seleccionar="seleccionarFecha" />

            <ModalPrioridad v-model:open="modalPrioridadAbierto" :prioridad-seleccionada="form.prioridad"
                @seleccionar="seleccionarPrioridad" />
        </DialogContent>
    </Dialog>
</template>
