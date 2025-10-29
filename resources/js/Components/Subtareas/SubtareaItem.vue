<script setup>
import { ref } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
	subtarea: {
		type: Object,
		required: true,
	},
})

const emit = defineEmits(['toggle', 'update', 'delete'])

const editando = ref(false)
const textoEditado = ref(props.subtarea.texto)

const toggleEstado = () => {
	emit('toggle')
}

const iniciarEdicion = () => {
	if (props.subtarea.estado === 'completada') return
	editando.value = true
	textoEditado.value = props.subtarea.texto
}

const guardarEdicion = () => {
	if (textoEditado.value.trim() === '') {
		textoEditado.value = props.subtarea.texto
		editando.value = false
		return
	}

	if (textoEditado.value !== props.subtarea.texto) {
		emit('update', textoEditado.value)
	}
	editando.value = false
}

const cancelarEdicion = () => {
	textoEditado.value = props.subtarea.texto
	editando.value = false
}

const eliminar = () => {
	emit('delete')
}
</script>

<template>
	<div
		class="group flex items-center gap-2 py-1.5 px-2 rounded hover:bg-neutral-50 transition-colors"
	>
		<!-- Checkbox para toggle estado -->
		<button
			type="button"
			@click="toggleEstado"
			class="flex-shrink-0 w-4 h-4 rounded border-2 transition-all"
			:class="
				subtarea.estado === 'completada'
					? 'bg-green-500 border-green-500'
					: 'border-neutral-300 hover:border-neutral-400'
			"
		>
			<svg
				v-if="subtarea.estado === 'completada'"
				class="w-full h-full text-white"
				fill="none"
				stroke="currentColor"
				viewBox="0 0 24 24"
			>
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					stroke-width="3"
					d="M5 13l4 4L19 7"
				/>
			</svg>
		</button>

		<!-- Texto editable -->
		<div class="flex-1 min-w-0">
			<input
				v-if="editando"
				v-model="textoEditado"
				type="text"
				maxlength="255"
				class="w-full px-2 py-0.5 text-sm border border-neutral-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
				@blur="guardarEdicion"
				@keydown.enter="guardarEdicion"
				@keydown.esc="cancelarEdicion"
				autofocus
			/>
			<button
				v-else
				type="button"
				@click="iniciarEdicion"
				class="w-full text-left text-sm transition-all"
				:class="
					subtarea.estado === 'completada'
						? 'line-through text-neutral-400'
						: 'text-neutral-700 hover:text-neutral-900'
				"
			>
				{{ subtarea.texto }}
			</button>
		</div>

		<!-- Botón eliminar -->
		<button
			type="button"
			@click="eliminar"
			class="flex-shrink-0 opacity-0 group-hover:opacity-100 text-neutral-400 hover:text-red-500 transition-all"
		>
			<X :size="14" />
		</button>
	</div>
</template>
