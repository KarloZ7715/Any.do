<script setup>
import { computed } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Check } from 'lucide-vue-next'

const props = defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    categoriaSeleccionada: {
        type: [Number, String, null],
        default: null,
    },
})

const emit = defineEmits(['update:open', 'seleccionar'])

const handleSeleccionar = (categoriaId) => {
    emit('seleccionar', categoriaId)
    emit('update:open', false)
}

const handleClose = () => {
    emit('update:open', false)
}

// Encontrar categoría Personal para mostrar por defecto
const categoriaPersonal = computed(() => 
    props.categorias.find(c => c.nombre === 'Personal'),
)
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white">
                    Seleccionar Categoría
                </DialogTitle>
            </DialogHeader>

            <div class="space-y-2 py-4">
                <!-- Opción: Sin categoría (Personal por defecto) -->
                <button
                    v-if="categoriaPersonal"
                    @click="handleSeleccionar(categoriaPersonal.id)"
                    :class="[
                        'w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200',
                        'border-2 hover:border-indigo-300 dark:hover:border-indigo-600',
                        categoriaSeleccionada === categoriaPersonal.id
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500 dark:border-indigo-400'
                            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700',
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="w-4 h-4 rounded"
                            :style="{ backgroundColor: categoriaPersonal.color }"
                        />
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ categoriaPersonal.nombre }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            (Por defecto)
                        </span>
                    </div>
                    <Check
                        v-if="categoriaSeleccionada === categoriaPersonal.id"
                        :size="20"
                        class="text-indigo-600 dark:text-indigo-400"
                    />
                </button>

                <!-- Otras categorías -->
                <button
                    v-for="categoria in categorias.filter(c => c.nombre !== 'Personal')"
                    :key="categoria.id"
                    @click="handleSeleccionar(categoria.id)"
                    :class="[
                        'w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200',
                        'border-2 hover:border-indigo-300 dark:hover:border-indigo-600',
                        categoriaSeleccionada === categoria.id
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500 dark:border-indigo-400'
                            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700',
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="w-4 h-4 rounded"
                            :style="{ backgroundColor: categoria.color }"
                        />
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ categoria.nombre }}
                        </span>
                    </div>
                    <Check
                        v-if="categoriaSeleccionada === categoria.id"
                        :size="20"
                        class="text-indigo-600 dark:text-indigo-400"
                    />
                </button>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button
                    variant="outline"
                    @click="handleClose"
                    class="transition-all duration-200"
                >
                    Cancelar
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
