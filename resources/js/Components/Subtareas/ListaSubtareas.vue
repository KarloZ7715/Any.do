<script setup>
import { ref, computed } from 'vue'
import { Check } from 'lucide-vue-next'
import SubtareaItem from '@/Components/Subtareas/SubtareaItem.vue'
import { usarSubtareas } from '@/composables/usarSubtareas'

const props = defineProps({
	tareaId: {
		type: [Number, String],
		required: true,
	},
	subtareas: {
		type: Array,
		default: () => [],
	},
})

const { crearSubtarea, actualizarSubtarea, eliminarSubtarea, toggleEstado } =
	usarSubtareas()

const nuevoTexto = ref('')
const estaCreando = ref(false)

const totalSubtareas = computed(() => props.subtareas.length)
const limiteAlcanzado = computed(() => totalSubtareas.value >= 30)
const completadas = computed(
	() => props.subtareas.filter((s) => s.estado === 'completada').length,
)

/**
 * Crear una nueva subtarea.
 */
const handleCrear = () => {
	if (!nuevoTexto.value.trim()) return
	if (limiteAlcanzado.value) {
		alert('Máximo 30 subtareas por tarea')
		return
	}

	estaCreando.value = true
	crearSubtarea(props.tareaId, nuevoTexto.value.trim())
	nuevoTexto.value = ''
	// estaCreando se resetea en el evento onFinish de Inertia
	setTimeout(() => {
		estaCreando.value = false
	}, 300)
}

/**
 * Manejar toggle de estado.
 */
const handleToggle = (subtarea) => {
	toggleEstado(props.tareaId, subtarea.id)
}

/**
 * Manejar actualización de texto.
 */
const handleUpdate = (subtarea, nuevoTexto) => {
	if (!nuevoTexto.trim()) return
	actualizarSubtarea(props.tareaId, subtarea.id, nuevoTexto.trim())
}

/**
 * Manejar eliminación.
 */
const handleDelete = (subtarea) => {
	eliminarSubtarea(props.tareaId, subtarea.id)
}

/**
 * Manejar Enter en input.
 */
const handleKeydown = (event) => {
	if (event.key === 'Enter') {
		event.preventDefault()
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
				class="text-xs text-neutral-500 dark:text-neutral-400"
				:class="{
					'text-amber-600 dark:text-amber-500': limiteAlcanzado,
				}"
			>
				{{ completadas }}/{{ totalSubtareas }} · Límite {{ totalSubtareas }}/30
			</span>
		</div>

		<!-- Contenedor invisible con scroll (max 5 visibles) -->
		<div
			v-if="subtareas.length > 0"
			class="max-h-[180px] space-y-1 overflow-y-auto"
		>
			<SubtareaItem
				v-for="subtarea in subtareas"
				:key="subtarea.id"
				:subtarea="subtarea"
				@toggle="handleToggle(subtarea)"
				@update="(nuevoTexto) => handleUpdate(subtarea, nuevoTexto)"
				@delete="handleDelete(subtarea)"
			/>
		</div>

		<!-- Mensaje si no hay subtareas -->
		<p
			v-else
			class="py-4 text-center text-xs text-neutral-400 dark:text-neutral-500"
		>
			Sin subtareas. Agrega una abajo.
		</p>

		<!-- Input para crear nueva subtarea -->
		<div class="flex items-center gap-2 pt-2">
			<div class="relative flex-1">
				<input
					v-model="nuevoTexto"
					type="text"
					placeholder="Nueva subtarea..."
					:disabled="limiteAlcanzado || estaCreando"
					class="w-full rounded-md border-neutral-300 px-3 py-1.5 text-sm transition-colors placeholder:text-neutral-400 focus:border-neutral-400 focus:ring-1 focus:ring-neutral-400 disabled:cursor-not-allowed disabled:bg-neutral-50 disabled:text-neutral-400 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500 dark:focus:border-neutral-600 dark:focus:ring-neutral-600 dark:disabled:bg-neutral-800"
					@keydown="handleKeydown"
				/>
			</div>

			<button
				type="button"
				:disabled="
					!nuevoTexto.trim() || limiteAlcanzado || estaCreando
				"
				class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-neutral-900 text-white transition-opacity hover:bg-neutral-800 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200"
				@click="handleCrear"
			>
				<Check :size="16" />
			</button>
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
