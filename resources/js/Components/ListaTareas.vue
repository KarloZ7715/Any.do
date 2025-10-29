<script setup>
import TareaCard from './TareaCard.vue'
import { FileQuestion } from 'lucide-vue-next'

const props = defineProps({
    tareas: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['toggle', 'edit', 'delete'])

const handleToggle = (id) => {
    emit('toggle', id)
}

const handleEdit = (tarea) => {
    emit('edit', tarea)
}

const handleDelete = (id) => {
    emit('delete', id)
}
</script>

<template>
    <div class="space-y-4">
        <!-- Lista de tareas -->
        <div v-if="tareas.length > 0" class="space-y-3">
            <TareaCard
                v-for="tarea in tareas"
                :key="tarea.id"
                :tarea="tarea"
                :loading="loading"
                @toggle="(id) => handleToggle(id)"
                @edit="(tarea) => handleEdit(tarea)"
                @delete="(id) => handleDelete(id)"
            />
        </div>

        <!-- Empty state -->
        <div
            v-else
            class="flex flex-col items-center justify-center py-12 px-4 text-center"
        >
            <FileQuestion class="h-16 w-16 text-muted-foreground/50 mb-4" />
            <h3 class="text-lg font-semibold text-foreground mb-2">
                No hay tareas
            </h3>
            <p class="text-sm text-muted-foreground max-w-sm">
                No se encontraron tareas con los filtros seleccionados. Intenta
                ajustar los filtros o crea una nueva tarea.
            </p>
        </div>
    </div>
</template>
