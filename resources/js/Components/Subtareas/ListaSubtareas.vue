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

// Lista local o de props según el modo
const subtareasMostrar = computed(() => {
	return props.modo === 'create' ? subtareasLocales.value : props.subtareas
})

const subtareasLocales = ref([...props.subtareas])

// Sincronizar subtareasLocales cuando cambien las props
watch(
	() => props.subtareas,
	(nuevas) => {
		if (props.modo === 'edit') {
			subtareasLocales.value = [...nuevas]
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

	if (props.modo === 'create') {
		// Modo crear: agregar localmente
		const nuevaSubtarea = {
			id: Date.now(), // ID temporal
			texto: nuevoTexto.value.trim(),
			estado: 'pendiente',
			tarea_id: null,
		}
		subtareasLocales.value.push(nuevaSubtarea)
		emit('crear', nuevaSubtarea)
	} else {
		// Modo editar: hacer petición al servidor
		emit('crear', nuevoTexto.value.trim())
	}

	nuevoTexto.value = ''
}

/**
 * Manejar toggle de estado.
 */
const handleToggle = (subtarea) => {
	if (props.modo === 'create') {
		// Modo crear: toggle local
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value[index].estado =
				subtareasLocales.value[index].estado === 'pendiente'
					? 'completada'
					: 'pendiente'
			emit('toggle', subtareasLocales.value[index])
		}
	} else {
		// Modo editar: hacer petición
		emit('toggle', subtarea)
	}
}

/**
 * Manejar actualización de texto.
 */
const handleUpdate = (subtarea, nuevoTextoParam) => {
	// Asegurar que nuevoTextoParam es string
	const nuevoTexto = typeof nuevoTextoParam === 'string' ? nuevoTextoParam : String(nuevoTextoParam || '')
	
	if (!nuevoTexto.trim()) return

	if (props.modo === 'create') {
		// Modo crear: actualizar localmente
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value[index].texto = nuevoTexto.trim()
			emit('actualizar', subtareasLocales.value[index])
		}
	} else {
		// Modo editar: hacer petición
		emit('actualizar', subtarea, nuevoTexto.trim())
	}
}

/**
 * Manejar eliminación.
 */
const handleDelete = (subtarea) => {
	if (props.modo === 'create') {
		// Modo crear: eliminar localmente sin confirmación
		const index = subtareasLocales.value.findIndex((s) => s.id === subtarea.id)
		if (index !== -1) {
			subtareasLocales.value.splice(index, 1)
			emit('eliminar', subtarea)
		}
	} else {
		// Modo editar: pedir confirmación y hacer petición
		emit('eliminar', subtarea)
	}
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
			<h3 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
				Subtareas
			</h3>
			<span
				v-if="totalSubtareas > 0"
				class="text-xs text-neutral-500 dark:text-neutral-400"
			>
				{{ completadas }}/{{ totalSubtareas }}
			</span>
		</div>

		<!-- Contenedor con scroll (max 5 visibles) -->
		<div
			v-if="subtareasMostrar.length > 0"
			class="max-h-[180px] space-y-1 overflow-y-auto"
		>
			<SubtareaItem
				v-for="subtarea in subtareasMostrar"
				:key="subtarea.id"
				:subtarea="subtarea"
				@toggle="handleToggle(subtarea)"
				@update="(nuevoTextoParam) => handleUpdate(subtarea, nuevoTextoParam)"
				@delete="handleDelete(subtarea)"
			/>
		</div>

		<!-- Input para crear nueva subtarea -->
		<div class="pt-2">
			<input
				v-model="nuevoTexto"
				type="text"
				placeholder="Nueva subtarea..."
				:disabled="limiteAlcanzado"
				class="w-full rounded-md border-neutral-300 px-3 py-1.5 text-sm transition-colors placeholder:text-neutral-400 focus:border-neutral-400 focus:ring-1 focus:ring-neutral-400 disabled:cursor-not-allowed disabled:bg-neutral-50 disabled:text-neutral-400 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500 dark:focus:border-neutral-600 dark:focus:ring-neutral-600 dark:disabled:bg-neutral-800"
				@keydown="handleKeydown"
				@blur="handleBlur"
			/>
		</div>

		<!-- Advertencia si límite alcanzado -->
		<p
			v-if="limiteAlcanzado"
			class="text-xs text-amber-600 dark:text-amber-500"
		>
			⚠️ Límite de 30 subtareas alcanzado
		</p>
	</div>
</template>
