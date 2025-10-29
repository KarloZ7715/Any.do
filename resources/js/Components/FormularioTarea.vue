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
		// Modo editar: hacer petición al servidor
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
		// Modo editar: hacer petición al servidor
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
		// Modo editar: hacer petición al servidor
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
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Título -->
        <div class="space-y-2">
            <Label for="titulo">Título *</Label>
            <Input
                id="titulo"
                v-model="form.titulo"
                type="text"
                placeholder="Ej: Completar informe mensual"
                :disabled="form.processing"
                maxlength="200"
                required
            />
            <p v-if="form.errors.titulo" class="text-sm text-red-600">
                {{ form.errors.titulo }}
            </p>
        </div>

        <!-- Descripción -->
        <div class="space-y-2">
            <Label for="descripcion">Descripción</Label>
            <Textarea
                id="descripcion"
                v-model="form.descripcion"
                placeholder="Describe los detalles de la tarea..."
                :disabled="form.processing"
                rows="4"
                maxlength="5000"
            />
            <p v-if="form.errors.descripcion" class="text-sm text-red-600">
                {{ form.errors.descripcion }}
            </p>
        </div>

        <!-- Grid de 2 columnas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Prioridad -->
            <div class="space-y-2">
                <Label for="prioridad">Prioridad *</Label>
                <Select v-model="form.prioridad" :disabled="form.processing">
                    <SelectTrigger id="prioridad">
                        <SelectValue placeholder="Selecciona prioridad" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="prioridad in prioridades"
                            :key="prioridad.value"
                            :value="prioridad.value"
                        >
                            {{ prioridad.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.prioridad" class="text-sm text-red-600">
                    {{ form.errors.prioridad }}
                </p>
            </div>

            <!-- Estado -->
            <div class="space-y-2">
                <Label for="estado">Estado *</Label>
                <Select v-model="form.estado" :disabled="form.processing">
                    <SelectTrigger id="estado">
                        <SelectValue placeholder="Selecciona estado" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="estado in estados"
                            :key="estado.value"
                            :value="estado.value"
                        >
                            {{ estado.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.estado" class="text-sm text-red-600">
                    {{ form.errors.estado }}
                </p>
            </div>
        </div>

        <!-- Grid de 2 columnas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Categoría -->
            <div class="space-y-2">
                <Label for="categoria">Categoría *</Label>
                <Select v-model:model-value="form.categoria_id" :disabled="form.processing">
                    <SelectTrigger id="categoria">
                        <SelectValue :placeholder="nombreCategoriaSeleccionada" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="categoria in categorias"
                            :key="categoria.id"
                            :value="categoria.id"
                        >
                            {{ categoria.nombre }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.categoria_id" class="text-sm text-red-600">
                    {{ form.errors.categoria_id }}
                </p>
            </div>

            <!-- Fecha de vencimiento -->
            <div class="space-y-2">
                <Label for="fecha_vencimiento">Fecha de vencimiento</Label>
                <Input
                    id="fecha_vencimiento"
                    v-model="form.fecha_vencimiento"
                    type="date"
                    :disabled="form.processing"
                    :min="new Date().toISOString().split('T')[0]"
                />
                <p
                    v-if="form.errors.fecha_vencimiento"
                    class="text-sm text-red-600"
                >
                    {{ form.errors.fecha_vencimiento }}
                </p>
            </div>
        </div>

        <!-- Subtareas (en ambos modos) -->
        <div class="space-y-2 border-t border-neutral-200 pt-4 dark:border-neutral-800">
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

        <!-- Botones -->
        <div class="flex justify-end gap-2 pt-4">
            <Button
                type="button"
                variant="outline"
                :disabled="form.processing"
                @click="handleCancel"
            >
                Cancelar
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{
                    form.processing
                        ? "Guardando..."
                        : tarea
                        ? "Actualizar"
                        : "Crear"
                }}
            </Button>
        </div>
    </form>
</template>
