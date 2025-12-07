<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { Button } from '@/Components/ui/button'
import ColorPicker from '@/Components/ColorPicker.vue'
import IconPicker from '@/Components/IconPicker.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    categoria: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['submit', 'cancel'])

const modoEdicion = computed(() => props.categoria !== null)

const esPersonal = computed(() => props.categoria?.es_personal === true)

// Formulario con valores iniciales
const form = useForm({
    nombre: props.categoria?.nombre || '',
    descripcion: props.categoria?.descripcion || '',
    color: props.categoria?.color || '#3B82F6',
    icono: props.categoria?.icono || 'user',
})

// Resetear form cuando cambia la categoría
watch(
    () => props.categoria,
    (nuevaCategoria) => {
        if (nuevaCategoria) {
            form.nombre = nuevaCategoria.nombre
            form.descripcion = nuevaCategoria.descripcion || ''
            form.color = nuevaCategoria.color
            form.icono = nuevaCategoria.icono || 'user'
        } else {
            form.reset()
        }
    },
)

const manejarSubmit = () => {
    // Si es categoría Personal, solo enviar color e icono
    const datos = esPersonal.value
        ? {
            color: form.color,
            icono: form.icono,
        }
        : {
            nombre: form.nombre,
            descripcion: form.descripcion,
            color: form.color,
            icono: form.icono,
        }

    emit('submit', datos)
}

const manejarCancelar = () => {
    form.reset()
    emit('cancel')
}
</script>

<template>
    <form @submit.prevent="manejarSubmit" class="space-y-6">
        <!-- Mensaje si es categoría Personal -->
        <div v-if="esPersonal" class="rounded-lg bg-primary/10 border border-primary/20 p-4 text-sm text-primary">
            <p class="font-medium">Categoría Personal (Protegida)</p>
            <p class="mt-1 text-primary/80">
                Solo puedes editar el color y el icono. El nombre no se puede cambiar.
            </p>
        </div>

        <!-- Layout de 2 columnas: Info + Apariencia -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Columna Izquierda: Información (solo si no es Personal) -->
            <div v-if="!esPersonal" class="space-y-4">
                <!-- Nombre -->
                <div class="space-y-2">
                    <Label for="nombre" class="text-sm font-medium">
                        Nombre <span class="text-red-500">*</span>
                    </Label>
                    <Input id="nombre" v-model="form.nombre" type="text" placeholder="Ej: Trabajo, Hogar, Estudios..."
                        maxlength="100" required :disabled="loading" class="text-sm" />
                    <InputError :message="form.errors.nombre" class="mt-1" />
                    <p class="text-xs text-muted-foreground">
                        Máximo 100 caracteres. Debe ser único.
                    </p>
                </div>

                <!-- Descripción -->
                <div class="space-y-2">
                    <Label for="descripcion" class="text-sm font-medium">
                        Descripción (opcional)
                    </Label>
                    <Textarea id="descripcion" v-model="form.descripcion" placeholder="Describe esta categoría..."
                        maxlength="500" rows="4" :disabled="loading" class="text-sm resize-none" />
                    <InputError :message="form.errors.descripcion" class="mt-1" />
                    <p class="text-xs text-muted-foreground">
                        Máximo 500 caracteres.
                    </p>
                </div>
            </div>

            <!-- Columna Derecha: Apariencia (Color + Icono) -->
            <div class="space-y-4" :class="{ 'md:col-span-2': esPersonal }">
                <!-- Color Picker -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium">Color</Label>
                    <ColorPicker v-model="form.color" />
                    <InputError :message="form.errors.color" class="mt-1" />
                </div>

                <!-- Icon Picker -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium">Icono</Label>
                    <IconPicker v-model="form.icono" />
                    <InputError :message="form.errors.icono" class="mt-1" />
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <Button type="button" variant="outline" :disabled="loading" @click="manejarCancelar">
                Cancelar
            </Button>
            <Button type="submit" :disabled="loading || form.processing" class="min-w-[100px]">
                <span v-if="loading || form.processing">Guardando...</span>
                <span v-else>{{ modoEdicion ? 'Actualizar' : 'Crear' }}</span>
            </Button>
        </div>
    </form>
</template>
