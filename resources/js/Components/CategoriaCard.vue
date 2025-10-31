<script setup>
import { computed } from 'vue'
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { Pencil, Trash2 } from 'lucide-vue-next'
import {
    Briefcase,
    User,
    BookOpen,
    Home,
    Heart,
    ShoppingCart,
    Dumbbell,
    Coffee,
    Laptop,
    Gamepad2,
    Music,
    Film,
    Camera,
    Plane,
    Car,
    Bike,
    UtensilsCrossed,
    GraduationCap,
    Code,
    Palette,
    Pill,
    DollarSign,
    Calendar,
    Clock,
} from 'lucide-vue-next'

const props = defineProps({
    categoria: {
        type: Object,
        required: true,
    },
    class: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['edit', 'delete'])

// Mapeo de iconos
const iconosDisponibles = {
    briefcase: Briefcase,
    user: User,
    'book-open': BookOpen,
    home: Home,
    heart: Heart,
    'shopping-cart': ShoppingCart,
    dumbbell: Dumbbell,
    coffee: Coffee,
    laptop: Laptop,
    'gamepad-2': Gamepad2,
    music: Music,
    film: Film,
    camera: Camera,
    plane: Plane,
    car: Car,
    bike: Bike,
    'utensils-crossed': UtensilsCrossed,
    'graduation-cap': GraduationCap,
    code: Code,
    palette: Palette,
    pill: Pill,
    'dollar-sign': DollarSign,
    calendar: Calendar,
    clock: Clock,
}

const IconoComponente = computed(() => {
    const icono = props.categoria.icono || 'user'
    return iconosDisponibles[icono] || User
})

const puedeEliminar = computed(() => !props.categoria.es_personal)
</script>

<template>
    <div
        :class="[
            'group relative bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600',
            props.class
        ]"
    >
        <!-- Badge de Protegida (si es Personal) -->
        <div v-if="categoria.es_personal" class="absolute top-2 right-2">
            <Badge variant="secondary" class="text-xs">
                Protegida
            </Badge>
        </div>

        <!-- Contenido Principal -->
        <div class="flex items-start gap-3">
            <!-- Círculo de Color con Icono -->
            <div
                class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                :style="{ backgroundColor: categoria.color }"
            >
                <component
                    :is="IconoComponente"
                    :size="24"
                    class="text-white drop-shadow-sm"
                />
            </div>

            <!-- Información -->
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                    {{ categoria.nombre }}
                </h3>
                <p
                    v-if="categoria.descripcion"
                    class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2"
                >
                    {{ categoria.descripcion }}
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-gray-500 dark:text-gray-500">
                        {{ categoria.tareas_count || 0 }}
                        {{ categoria.tareas_count === 1 ? 'tarea' : 'tareas' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
            <!-- Botón Editar -->
            <Button
                variant="ghost"
                size="sm"
                class="flex-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="emit('edit', categoria.id)"
            >
                <Pencil :size="16" class="mr-1.5" />
                Editar
            </Button>

            <!-- Botón Eliminar -->
            <Button
                variant="ghost"
                size="sm"
                :disabled="!puedeEliminar"
                :class="[
                    'flex-1',
                    puedeEliminar
                        ? 'text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20'
                        : 'text-gray-400 dark:text-gray-600 cursor-not-allowed',
                ]"
                @click="puedeEliminar && emit('delete', categoria.id)"
            >
                <Trash2 :size="16" class="mr-1.5" />
                Eliminar
            </Button>
        </div>
    </div>
</template>
