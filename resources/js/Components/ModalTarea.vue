<script setup>
import { computed } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog'
import FormularioTarea from '@/Components/FormularioTarea.vue'

const props = defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    mode: {
        type: String,
        default: 'create',
        validator: (value) => ['create', 'edit'].includes(value),
    },
    tarea: {
        type: Object,
        default: null,
    },
    categorias: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['close', 'submit'])

const handleClose = () => {
    emit('close')
}

const handleSubmit = (form) => {
    emit('submit', form)
}

const handleCancel = () => {
    emit('close')
}

const titulo = computed(() => {
    return props.mode === 'create' ? 'Crear nueva tarea' : 'Editar tarea'
})

const descripcion = computed(() => {
    return props.mode === 'create'
        ? 'Completa los campos para crear una nueva tarea.'
        : 'Modifica los campos para actualizar la tarea.'
})
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="w-[95vw] max-w-[600px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ titulo }}</DialogTitle>
                <DialogDescription>
                    {{ descripcion }}
                </DialogDescription>
            </DialogHeader>

            <FormularioTarea
                :tarea="tarea"
                :categorias="categorias"
                @submit="handleSubmit"
                @cancel="handleCancel"
            />
        </DialogContent>
    </Dialog>
</template>
