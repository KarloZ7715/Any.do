<script setup>
import { computed } from 'vue'
import { Check } from 'lucide-vue-next'

const props = defineProps({
	checked: {
		type: Boolean,
		default: false,
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	size: {
		type: String,
		default: 'md', // 'sm', 'md', 'lg'
	},
})

const emit = defineEmits(['update:checked'])

const sizeClasses = computed(() => {
	const sizes = {
		sm: 'w-4 h-4',
		md: 'w-5 h-5',
		lg: 'w-6 h-6',
	}
	return sizes[props.size] || sizes.md
})

const iconSize = computed(() => {
	const sizes = {
		sm: 12,
		md: 14,
		lg: 18,
	}
	return sizes[props.size] || sizes.md
})

const handleClick = () => {
	if (!props.disabled) {
		emit('update:checked', !props.checked)
	}
}
</script>

<template>
	<button type="button" @click="handleClick" :disabled="disabled" :class="[
		'relative flex items-center justify-center rounded-full border-2 transition-all duration-300 ease-out',
		sizeClasses,
		checked
			? 'bg-primary border-primary scale-100'
			: 'bg-card border-[color:var(--task-checkbox-border)] hover:border-primary',
		disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:scale-110 active:scale-95',
	]">
		<!-- Check icon con animación -->
		<Transition enter-active-class="transition-all duration-200 ease-out"
			enter-from-class="scale-0 rotate-90 opacity-0" enter-to-class="scale-100 rotate-0 opacity-100"
			leave-active-class="transition-all duration-150 ease-in" leave-from-class="scale-100 rotate-0 opacity-100"
			leave-to-class="scale-0 rotate-90 opacity-0">
			<Check v-if="checked" :size="iconSize" class="text-primary-foreground stroke-[3]" />
		</Transition>

		<!-- Ripple effect (opcional) -->
		<span v-if="checked" class="absolute inset-0 rounded-full bg-primary animate-ping opacity-20" />
	</button>
</template>

<style scoped>
@keyframes ping {
	0% {
		transform: scale(1);
		opacity: 0.2;
	}

	50% {
		opacity: 0.1;
	}

	100% {
		transform: scale(1.5);
		opacity: 0;
	}
}

.animate-ping {
	animation: ping 0.6s cubic-bezier(0, 0, 0.2, 1) 1;
}
</style>
