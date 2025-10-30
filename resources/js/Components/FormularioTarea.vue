<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select'
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
})

const emit = defineEmits(['submit', 'cancel'])

const { crearSubtarea, actualizarSubtarea, eliminarSubtarea, toggleEstado } =
	usarSubtareas()

// Subtareas locales (para modo crear)
const subtareasLocales = ref([])

// Encontrar categoría "Personal" para usar por defecto solo en creación
const categoriaPersonal = props.categorias.find((c) => c.nombre === 'Personal')

// Determinar categoría inicial
const obtenerCategoriaInicial = () => {
    // Si estamos editando una tarea
    if (props.tarea) {
        // Usar la categoría actual de la tarea (puede ser null)
        return props.tarea.categoria_id
    }
    // Si estamos creando, usar Personal por defecto
    return categoriaPersonal?.id || null
}

// Computed para obtener nombre de la categoría seleccionada
const nombreCategoriaSeleccionada = computed(() => {
    const categoriaId = form.categoria_id
    if (!categoriaId) return 'Selecciona una categoría'
    const categoria = props.categorias.find((c) => c.id === categoriaId)
    return categoria?.nombre || 'Selecciona una categoría'
})

// Inicializar formulario
const form = useForm({
    titulo: props.tarea?.titulo || '',
    descripcion: props.tarea?.descripcion || '',
    estado: props.tarea?.estado || 'pendiente',
    prioridad: props.tarea?.prioridad || 2,
    fecha_vencimiento: props.tarea?.fecha_vencimiento || null,
    categoria_id: obtenerCategoriaInicial(),
})

const handleSubmit = () => {
    emit('submit', form)
}

const handleCancel = () => {
    emit('cancel')
}

// Handlers para subtareas
const handleCrearSubtarea = (data) => {
	if (props.tarea) {
		// Modo editar: hacer petición al servidor
		crearSubtarea(props.tarea.id, data)
	} else {
		// Modo crear: agregar localmente
		subtareasLocales.value.push(data)
	}
}

const handleActualizarSubtarea = (subtarea, nuevoTexto) => {
	if (props.tarea) {
		// Modo editar: actualización optimista + petición al servidor
		// 1. Actualizar UI inmediatamente (optimista)
		const index = props.tarea.subtareas?.data 
			? props.tarea.subtareas.data.findIndex((s) => s.id === subtarea.id)
			: props.tarea.subtareas?.findIndex((s) => s.id === subtarea.id)
		
		if (index !== -1) {
			const subtareasArray = props.tarea.subtareas?.data || props.tarea.subtareas
			subtareasArray[index] = { ...subtareasArray[index], texto: nuevoTexto }
		}
		
		// 2. Hacer petición al servidor
		actualizarSubtarea(props.tarea.id, subtarea.id, nuevoTexto)
	} else {
		// Modo crear: actualizar localmente
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value[index] = { ...subtarea, texto: nuevoTexto }
		}
	}
}

const handleEliminarSubtarea = (subtarea) => {
	if (props.tarea) {
		// Modo editar: actualización optimista + petición al servidor
		// 1. Actualizar UI inmediatamente (optimista)
		const subtareasArray = props.tarea.subtareas?.data || props.tarea.subtareas
		const index = subtareasArray?.findIndex((s) => s.id === subtarea.id)
		
		if (index !== -1) {
			subtareasArray.splice(index, 1)
		}
		
		// 2. Hacer petición al servidor
		eliminarSubtarea(props.tarea.id, subtarea.id)
	} else {
		// Modo crear: eliminar localmente
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value.splice(index, 1)
		}
	}
}

const handleToggleSubtarea = (subtarea) => {
	if (props.tarea) {
		// Modo editar: actualización optimista + petición al servidor
		// 1. Actualizar UI inmediatamente (optimista)
		const subtareasArray = props.tarea.subtareas?.data || props.tarea.subtareas
		const index = subtareasArray?.findIndex((s) => s.id === subtarea.id)
		
		if (index !== -1) {
			const nuevoEstado = subtareasArray[index].estado === 'pendiente' ? 'completada' : 'pendiente'
			subtareasArray[index] = { ...subtareasArray[index], estado: nuevoEstado }
		}
		
		// 2. Hacer petición al servidor
		toggleEstado(props.tarea.id, subtarea.id)
	} else {
		// Modo crear: toggle localmente
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value[index].estado =
				subtareasLocales.value[index].estado === 'pendiente'
					? 'completada'
					: 'pendiente'
		}
	}
}

const prioridades = [
    { value: 1, label: 'Alta' },
    { value: 2, label: 'Media' },
    { value: 3, label: 'Baja' },
]

const estados = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'completada', label: 'Completada' },
]
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Título -->
        <div class="space-y-2">
            <Label for="titulo" class="text-sm font-semibold text-gray-900 dark:text-white">
                Título de la tarea
                <span class="text-red-500">*</span>
            </Label>
            <Input
                id="titulo"
                v-model="form.titulo"
                type="text"
                placeholder="Ej: Completar informe mensual"
                :disabled="form.processing"
                maxlength="200"
                required
                class="text-base transition-all duration-200"
                :class="{
                    'border-red-300 dark:border-red-700 focus:border-red-500 dark:focus:border-red-500': form.errors.titulo,
                    'focus:border-indigo-500 dark:focus:border-indigo-400': !form.errors.titulo,
                }"
            />
            <p v-if="form.errors.titulo" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                <span class="font-medium">⚠️</span>
                {{ form.errors.titulo }}
            </p>
            <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                Máximo 200 caracteres ({{ form.titulo?.length || 0 }}/200)
            </p>
        </div>

        <!-- Descripción -->
        <div class="space-y-2">
            <Label for="descripcion" class="text-sm font-semibold text-gray-900 dark:text-white">
                Descripción
                <span class="text-gray-400 text-xs font-normal">(opcional)</span>
            </Label>
            <Textarea
                id="descripcion"
                v-model="form.descripcion"
                placeholder="Agrega detalles adicionales sobre la tarea..."
                :disabled="form.processing"
                rows="4"
                maxlength="5000"
                class="resize-none transition-all duration-200"
                :class="{
                    'border-red-300 dark:border-red-700 focus:border-red-500 dark:focus:border-red-500': form.errors.descripcion,
                    'focus:border-indigo-500 dark:focus:border-indigo-400': !form.errors.descripcion,
                }"
            />
            <p v-if="form.errors.descripcion" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                <span class="font-medium">⚠️</span>
                {{ form.errors.descripcion }}
            </p>
            <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                Máximo 5000 caracteres ({{ form.descripcion?.length || 0 }}/5000)
            </p>
        </div>

        <!-- Grid de 2 columnas - Prioridad y Estado -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Prioridad -->
            <div class="space-y-2">
                <Label for="prioridad" class="text-sm font-semibold text-gray-900 dark:text-white">
                    Prioridad
                    <span class="text-red-500">*</span>
                </Label>
                <Select v-model="form.prioridad" :disabled="form.processing">
                    <SelectTrigger id="prioridad" class="transition-all duration-200">
                        <SelectValue placeholder="Selecciona prioridad" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="prioridad in prioridades"
                            :key="prioridad.value"
                            :value="prioridad.value"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-2 h-2 rounded-full"
                                    :class="{
                                        'bg-red-500': prioridad.value === 1,
                                        'bg-yellow-500': prioridad.value === 2,
                                        'bg-green-500': prioridad.value === 3,
                                    }"
                                />
                                {{ prioridad.label }}
                            </div>
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.prioridad" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                    <span class="font-medium">⚠️</span>
                    {{ form.errors.prioridad }}
                </p>
                <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                    Define la urgencia de tu tarea
                </p>
            </div>

            <!-- Estado -->
            <div class="space-y-2">
                <Label for="estado" class="text-sm font-semibold text-gray-900 dark:text-white">
                    Estado
                    <span class="text-red-500">*</span>
                </Label>
                <Select v-model="form.estado" :disabled="form.processing">
                    <SelectTrigger id="estado" class="transition-all duration-200">
                        <SelectValue placeholder="Selecciona estado" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="estado in estados"
                            :key="estado.value"
                            :value="estado.value"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-2 h-2 rounded-full"
                                    :class="{
                                        'bg-blue-500': estado.value === 'pendiente',
                                        'bg-green-500': estado.value === 'completada',
                                    }"
                                />
                                {{ estado.label }}
                            </div>
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.estado" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                    <span class="font-medium">⚠️</span>
                    {{ form.errors.estado }}
                </p>
                <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                    Estado actual de la tarea
                </p>
            </div>
        </div>

        <!-- Grid de 2 columnas - Categoría y Fecha -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Categoría -->
            <div class="space-y-2">
                <Label for="categoria" class="text-sm font-semibold text-gray-900 dark:text-white">
                    Categoría
                    <span class="text-red-500">*</span>
                </Label>
                <Select v-model:model-value="form.categoria_id" :disabled="form.processing">
                    <SelectTrigger id="categoria" class="transition-all duration-200">
                        <SelectValue :placeholder="nombreCategoriaSeleccionada" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="categoria in categorias"
                            :key="categoria.id"
                            :value="categoria.id"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-3 h-3 rounded"
                                    :style="{ backgroundColor: categoria.color }"
                                />
                                {{ categoria.nombre }}
                            </div>
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.categoria_id" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                    <span class="font-medium">⚠️</span>
                    {{ form.errors.categoria_id }}
                </p>
                <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                    Organiza tus tareas por categoría
                </p>
            </div>

            <!-- Fecha de vencimiento -->
            <div class="space-y-2">
                <Label for="fecha_vencimiento" class="text-sm font-semibold text-gray-900 dark:text-white">
                    Fecha de vencimiento
                    <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                </Label>
                <Input
                    id="fecha_vencimiento"
                    v-model="form.fecha_vencimiento"
                    type="date"
                    :disabled="form.processing"
                    :min="new Date().toISOString().split('T')[0]"
                    class="transition-all duration-200"
                    :class="{
                        'border-red-300 dark:border-red-700 focus:border-red-500 dark:focus:border-red-500': form.errors.fecha_vencimiento,
                        'focus:border-indigo-500 dark:focus:border-indigo-400': !form.errors.fecha_vencimiento,
                    }"
                />
                <p
                    v-if="form.errors.fecha_vencimiento"
                    class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1"
                >
                    <span class="font-medium">⚠️</span>
                    {{ form.errors.fecha_vencimiento }}
                </p>
                <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                    ¿Cuándo debe completarse?
                </p>
            </div>
        </div>

        <!-- Subtareas Section -->
        <div class="space-y-3 border-t border-gray-200 dark:border-gray-800 pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <Label class="text-sm font-semibold text-gray-900 dark:text-white">
                        Subtareas
                        <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                    </Label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Divide tu tarea en pasos más pequeños
                    </p>
                </div>
            </div>
            <ListaSubtareas
                :tarea-id="tarea?.id"
                :subtareas="tarea ? (tarea.subtareas?.data || tarea.subtareas || []) : subtareasLocales"
                :modo="tarea ? 'edit' : 'create'"
                @crear="handleCrearSubtarea"
                @actualizar="handleActualizarSubtarea"
                @eliminar="handleEliminarSubtarea"
                @toggle="handleToggleSubtarea"
            />
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-between gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
            <!-- Info de estado -->
            <div v-if="form.processing" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Guardando cambios...</span>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                <span v-if="tarea">Editando tarea existente</span>
                <span v-else>Creando nueva tarea</span>
            </div>

            <!-- Botones -->
            <div class="flex gap-3">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="form.processing"
                    @click="handleCancel"
                    class="min-w-[100px] transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    Cancelar
                </Button>
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="min-w-[100px] transition-all duration-200 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                >
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else-if="tarea">Actualizar tarea</span>
                    <span v-else>Crear tarea</span>
                </Button>
            </div>
        </div>
    </form>
</template>
