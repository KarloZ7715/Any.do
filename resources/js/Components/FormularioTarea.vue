<script setup>
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/Components/ui/form'

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

// Inicializar formulario
const form = useForm({
    titulo: props.tarea?.titulo || '',
    descripcion: props.tarea?.descripcion || '',
    estado: props.tarea?.estado || 'pendiente',
    prioridad: props.tarea?.prioridad || 2,
    fecha_vencimiento: props.tarea?.fecha_vencimiento || null,
    categoria_id: props.tarea?.categoria_id || null,
})

const handleSubmit = () => {
    emit('submit', form)
}

const handleCancel = () => {
    emit('cancel')
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
        <FormField v-slot="{ componentField }" name="titulo">
            <FormItem>
                <FormLabel>Título *</FormLabel>
                <FormControl>
                    <Input
                        v-model="form.titulo"
                        v-bind="componentField"
                        type="text"
                        placeholder="Ej: Completar informe mensual"
                        :disabled="form.processing"
                        maxlength="200"
                        required
                    />
                </FormControl>
                <FormMessage v-if="form.errors.titulo">
                    {{ form.errors.titulo }}
                </FormMessage>
            </FormItem>
        </FormField>

        <!-- Descripción -->
        <FormField v-slot="{ componentField }" name="descripcion">
            <FormItem>
                <FormLabel>Descripción</FormLabel>
                <FormControl>
                    <Textarea
                        v-model="form.descripcion"
                        v-bind="componentField"
                        placeholder="Describe los detalles de la tarea..."
                        :disabled="form.processing"
                        rows="4"
                        maxlength="5000"
                    />
                </FormControl>
                <FormMessage v-if="form.errors.descripcion">
                    {{ form.errors.descripcion }}
                </FormMessage>
            </FormItem>
        </FormField>

        <!-- Grid de 2 columnas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Prioridad -->
            <FormField v-slot="{ componentField }" name="prioridad">
                <FormItem>
                    <FormLabel>Prioridad *</FormLabel>
                    <Select v-model="form.prioridad" :disabled="form.processing">
                        <FormControl>
                            <SelectTrigger v-bind="componentField">
                                <SelectValue placeholder="Selecciona prioridad" />
                            </SelectTrigger>
                        </FormControl>
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
                    <FormMessage v-if="form.errors.prioridad">
                        {{ form.errors.prioridad }}
                    </FormMessage>
                </FormItem>
            </FormField>

            <!-- Estado -->
            <FormField v-slot="{ componentField }" name="estado">
                <FormItem>
                    <FormLabel>Estado *</FormLabel>
                    <Select v-model="form.estado" :disabled="form.processing">
                        <FormControl>
                            <SelectTrigger v-bind="componentField">
                                <SelectValue placeholder="Selecciona estado" />
                            </SelectTrigger>
                        </FormControl>
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
                    <FormMessage v-if="form.errors.estado">
                        {{ form.errors.estado }}
                    </FormMessage>
                </FormItem>
            </FormField>
        </div>

        <!-- Grid de 2 columnas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Categoría -->
            <FormField v-slot="{ componentField }" name="categoria_id">
                <FormItem>
                    <FormLabel>Categoría</FormLabel>
                    <Select v-model="form.categoria_id" :disabled="form.processing">
                        <FormControl>
                            <SelectTrigger v-bind="componentField">
                                <SelectValue placeholder="Sin categoría" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem :value="null">
                                Sin categoría
                            </SelectItem>
                            <SelectItem
                                v-for="categoria in categorias"
                                :key="categoria.id"
                                :value="categoria.id"
                            >
                                {{ categoria.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage v-if="form.errors.categoria_id">
                        {{ form.errors.categoria_id }}
                    </FormMessage>
                </FormItem>
            </FormField>

            <!-- Fecha de vencimiento -->
            <FormField v-slot="{ componentField }" name="fecha_vencimiento">
                <FormItem>
                    <FormLabel>Fecha de vencimiento</FormLabel>
                    <FormControl>
                        <Input
                            v-model="form.fecha_vencimiento"
                            v-bind="componentField"
                            type="date"
                            :disabled="form.processing"
                            :min="new Date().toISOString().split('T')[0]"
                        />
                    </FormControl>
                    <FormMessage v-if="form.errors.fecha_vencimiento">
                        {{ form.errors.fecha_vencimiento }}
                    </FormMessage>
                </FormItem>
            </FormField>
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
            <Button
                type="submit"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Guardando...' : (tarea ? 'Actualizar' : 'Crear') }}
            </Button>
        </div>
    </form>
</template>
