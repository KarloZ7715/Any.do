<script setup>
import { ref, computed } from 'vue'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'

const props = defineProps({
    modelValue: {
        type: String,
        default: '#3B82F6',
    },
})

const emit = defineEmits(['update:modelValue'])

// Paleta de colores predefinidos (diseño profesional)
const coloresPredefinidos = [
    { nombre: 'Azul', valor: '#3B82F6', clase: 'bg-blue-500' },
    { nombre: 'Verde', valor: '#10B981', clase: 'bg-green-500' },
    { nombre: 'Amarillo', valor: '#F59E0B', clase: 'bg-amber-500' },
    { nombre: 'Rojo', valor: '#EF4444', clase: 'bg-red-500' },
    { nombre: 'Morado', valor: '#8B5CF6', clase: 'bg-violet-500' },
    { nombre: 'Rosa', valor: '#EC4899', clase: 'bg-pink-500' },
    { nombre: 'Naranja', valor: '#F97316', clase: 'bg-orange-500' },
    { nombre: 'Gris', valor: '#6B7280', clase: 'bg-gray-500' },
    { nombre: 'Índigo', valor: '#6366F1', clase: 'bg-indigo-500' },
    { nombre: 'Cian', valor: '#06B6D4', clase: 'bg-cyan-500' },
    { nombre: 'Esmeralda', valor: '#059669', clase: 'bg-emerald-600' },
    { nombre: 'Lima', valor: '#84CC16', clase: 'bg-lime-500' },
]

const colorManual = ref(props.modelValue)

const colorSeleccionado = computed({
    get: () => props.modelValue,
    set: (valor) => emit('update:modelValue', valor),
})

const seleccionarColor = (color) => {
    colorSeleccionado.value = color
    colorManual.value = color
}

const actualizarColorManual = (event) => {
    const valor = event.target.value
    colorSeleccionado.value = valor
}

const esColorActivo = (color) => {
    return colorSeleccionado.value.toLowerCase() === color.toLowerCase()
}
</script>

<template>
    <div class="space-y-3">
        <!-- Preview compacto del color seleccionado -->
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-md border-2 border-border shadow-sm"
                :style="{ backgroundColor: colorSeleccionado }"></div>
            <div class="flex-1">
                <Label class="text-sm font-medium">Color seleccionado</Label>
                <p class="text-xs text-muted-foreground mt-0.5">{{ colorSeleccionado }}</p>
            </div>
        </div>

        <!-- Grid de colores predefinidos (8 colores principales) -->
        <div>
            <Label class="text-sm font-medium mb-2 block">Colores predefinidos</Label>
            <div class="grid grid-cols-8 gap-1.5">
                <button v-for="color in coloresPredefinidos.slice(0, 8)" :key="color.valor" type="button"
                    class="group relative h-8 w-full rounded-md border-2 transition-all hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-background"
                    :class="[
                        color.clase,
                        esColorActivo(color.valor)
                            ? 'border-foreground ring-2 ring-foreground ring-offset-1 scale-110'
                            : 'border-transparent hover:border-border',
                    ]" :title="color.nombre" @click="seleccionarColor(color.valor)">
                    <!-- Checkmark cuando está seleccionado -->
                    <span v-if="esColorActivo(color.valor)" class="absolute inset-0 flex items-center justify-center">
                        <svg class="h-4 w-4 text-white drop-shadow-lg" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>

        <!-- Input manual de color (compacto) -->
        <div class="space-y-1.5">
            <Label for="color-manual" class="text-xs font-medium text-muted-foreground">
                Color personalizado
            </Label>
            <div class="flex gap-2">
                <Input id="color-manual" v-model="colorManual" type="text" placeholder="#3B82F6" maxlength="7"
                    class="flex-1 font-mono text-xs h-9" @input="actualizarColorManual" />
                <input type="color" :value="colorSeleccionado"
                    class="h-9 w-9 cursor-pointer rounded border border-input bg-card" @input="actualizarColorManual" />
            </div>
        </div>
    </div>
</template>
