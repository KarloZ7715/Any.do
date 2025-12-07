<script setup>
import { ref, computed, watch } from 'vue'
import SubtareaItem from '@/Components/Subtareas/SubtareaItem.vue'

const props = defineProps({
	tareaId: {
		type: [Number, String],
		required: false,
	},
	subtareas: {
		type: Array,
		default: () => [],
	},
	modo: {
		type: String,
		default: 'edit', // 'edit' o 'create'
	},
})

const emit = defineEmits(['crear', 'actualizar', 'eliminar', 'toggle'])

const nuevoTexto = ref('')

// Estado local solo para modo 'create'
const subtareasLocales = ref([])

// Inicializar subtareas locales solo si es modo create
if (props.modo === 'create') {
	subtareasLocales.value = [...(props.subtareas || [])]
}

// Lista a mostrar según el modo
const subtareasMostrar = computed(() => {
	return props.modo === 'create' ? subtareasLocales.value : props.subtareas
})

// Sincronizar subtareasLocales cuando cambien las props (solo en modo create)
watch(
	() => props.subtareas,
	(nuevas) => {
		if (props.modo === 'create') {
			subtareasLocales.value = [...(nuevas || [])]
		}
	},
	{ deep: true },
)

const totalSubtareas = computed(() => subtareasMostrar.value.length)
const limiteAlcanzado = computed(() => totalSubtareas.value >= 30)
const completadas = computed(
	() =>
		subtareasMostrar.value.filter((s) => s.estado === 'completada').length,
)

/**
 * Crear una nueva subtarea (emite evento al padre o crea localmente).
 */
const handleCrear = () => {
	if (!nuevoTexto.value.trim()) return
	if (limiteAlcanzado.value) {
		alert('Máximo 30 subtareas por tarea')
		return
	}

	// Crear optimistamente solo en modo create
	if (props.modo === 'create') {
		const nuevaSubtarea = {
			id: Date.now(), // ID temporal
			texto: nuevoTexto.value.trim(),
			estado: 'pendiente',
			tarea_id: props.tareaId || null,
		}

		subtareasLocales.value.push(nuevaSubtarea)
		emit('crear', nuevaSubtarea)
	} else {
		// Modo editar: solo emitir, el padre maneja la actualización optimista
		emit('crear', nuevoTexto.value.trim())
	}

	nuevoTexto.value = ''
}

/**
 * Manejar toggle de estado.
 */
const handleToggle = (subtarea) => {
	// En modo create, toggle optimista local
	if (props.modo === 'create') {
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value[index].estado =
				subtareasLocales.value[index].estado === 'pendiente'
					? 'completada'
					: 'pendiente'
		}
	}

	// Emitir para que el padre maneje (importante en modo edit)
	emit('toggle', subtarea)
}

/**
 * Manejar actualización de texto.
 */
const handleUpdate = (subtarea, nuevoTextoParam) => {
	// Asegurar que nuevoTextoParam es string
	const nuevoTexto = typeof nuevoTextoParam === 'string' ? nuevoTextoParam : String(nuevoTextoParam || '')

	if (!nuevoTexto.trim()) return

	// Actualizar optimistamente solo en modo create
	if (props.modo === 'create') {
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value[index].texto = nuevoTexto.trim()
		}
		emit('actualizar', subtareasLocales.value[index])
	} else {
		// Modo editar: solo emitir, el padre maneja la actualización optimista
		emit('actualizar', subtarea, nuevoTexto.trim())
	}
}

/**
 * Manejar eliminación.
 */
const handleDelete = (subtarea) => {
	// Eliminar optimistamente solo en modo create
	if (props.modo === 'create') {
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value.splice(index, 1)
		}
	}

	// Emitir para que el padre maneje (importante en modo edit)
	emit('eliminar', subtarea)
}

/**
 * Manejar Enter o blur en input.
 */
const handleKeydown = (event) => {
	if (event.key === 'Enter') {
		event.preventDefault()
		handleCrear()
	}
}

const handleBlur = () => {
	// Crear subtarea al perder foco si hay texto
	if (nuevoTexto.value.trim()) {
		handleCrear()
	}
}
</script>

<template>
	<div class="space-y-2">
		<!-- Header con título y contador -->
		<div class="flex items-center justify-between">
			<h3 class="text-xs font-medium text-muted-foreground">
				Subtareas
			</h3>
			<span v-if="totalSubtareas > 0" class="text-xs text-muted-foreground">
				{{ completadas }}/{{ totalSubtareas }}
			</span>
		</div>

		<!-- Contenedor con scroll (max 5 visibles) -->
		<div v-if="subtareasMostrar.length > 0" class="max-h-[180px] space-y-1 overflow-y-auto">
			<SubtareaItem v-for="subtarea in subtareasMostrar" :key="subtarea.id" :subtarea="subtarea"
				@toggle="handleToggle(subtarea)" @update="(nuevoTextoParam) => handleUpdate(subtarea, nuevoTextoParam)"
				@delete="handleDelete(subtarea)" />
		</div>

		<!-- Input para crear nueva subtarea -->
		<div class="pt-2">
			<input v-model="nuevoTexto" type="text" placeholder="Nueva subtarea..." :disabled="limiteAlcanzado"
				class="w-full rounded-md border-input px-3 py-1.5 text-sm transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:bg-muted disabled:text-muted-foreground bg-transparent text-foreground"
				@keydown="handleKeydown" @blur="handleBlur" />
		</div>

		<!-- Advertencia si límite alcanzado -->
		<p v-if="limiteAlcanzado" class="text-xs text-amber-600 dark:text-amber-500">
			⚠️ Límite de 30 subtareas alcanzado
		</p>
	</div>
</template>
