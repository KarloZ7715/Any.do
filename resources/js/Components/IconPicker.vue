<script setup>
import { ref, computed } from 'vue'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
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
    modelValue: {
        type: String,
        default: 'user',
    },
})

const emit = defineEmits(['update:modelValue'])

// Iconos disponibles (Lucide)
const iconosDisponibles = [
    { nombre: 'Trabajo', valor: 'briefcase', componente: Briefcase },
    { nombre: 'Usuario', valor: 'user', componente: User },
    { nombre: 'Libros', valor: 'book-open', componente: BookOpen },
    { nombre: 'Hogar', valor: 'home', componente: Home },
    { nombre: 'Corazón', valor: 'heart', componente: Heart },
    { nombre: 'Compras', valor: 'shopping-cart', componente: ShoppingCart },
    { nombre: 'Gimnasio', valor: 'dumbbell', componente: Dumbbell },
    { nombre: 'Café', valor: 'coffee', componente: Coffee },
    { nombre: 'Laptop', valor: 'laptop', componente: Laptop },
    { nombre: 'Juegos', valor: 'gamepad-2', componente: Gamepad2 },
    { nombre: 'Música', valor: 'music', componente: Music },
    { nombre: 'Película', valor: 'film', componente: Film },
    { nombre: 'Cámara', valor: 'camera', componente: Camera },
    { nombre: 'Viaje', valor: 'plane', componente: Plane },
    { nombre: 'Auto', valor: 'car', componente: Car },
    { nombre: 'Bicicleta', valor: 'bike', componente: Bike },
    { nombre: 'Comida', valor: 'utensils-crossed', componente: UtensilsCrossed },
    { nombre: 'Educación', valor: 'graduation-cap', componente: GraduationCap },
    { nombre: 'Código', valor: 'code', componente: Code },
    { nombre: 'Arte', valor: 'palette', componente: Palette },
    { nombre: 'Salud', valor: 'pill', componente: Pill },
    { nombre: 'Dinero', valor: 'dollar-sign', componente: DollarSign },
    { nombre: 'Calendario', valor: 'calendar', componente: Calendar },
    { nombre: 'Reloj', valor: 'clock', componente: Clock },
]

const busqueda = ref('')

const iconoSeleccionado = computed({
    get: () => props.modelValue,
    set: (valor) => emit('update:modelValue', valor),
})

const iconosFiltrados = computed(() => {
    if (!busqueda.value) return iconosDisponibles

    const termino = busqueda.value.toLowerCase()
    return iconosDisponibles.filter(
        (icono) =>
            icono.nombre.toLowerCase().includes(termino) ||
            icono.valor.toLowerCase().includes(termino),
    )
})

const iconoActualComponente = computed(() => {
    const icono = iconosDisponibles.find((i) => i.valor === iconoSeleccionado.value)
    return icono ? icono.componente : User
})

const seleccionarIcono = (icono) => {
    iconoSeleccionado.value = icono.valor
}

const esIconoActivo = (icono) => {
    return iconoSeleccionado.value === icono.valor
}
</script>

<template>
    <div class="space-y-4">
        <!-- Preview del icono seleccionado -->
        <div class="flex items-center gap-3">
            <div
                class="flex h-16 w-16 items-center justify-center rounded-lg border-2 border-gray-200 bg-gray-50 shadow-sm"
            >
                <component :is="iconoActualComponente" class="h-8 w-8 text-gray-700" />
            </div>
            <div class="flex-1">
                <Label class="text-sm font-medium">Icono seleccionado</Label>
                <p class="text-xs text-gray-500 mt-1">{{ iconoSeleccionado }}</p>
            </div>
        </div>

        <!-- Búsqueda de iconos -->
        <div class="space-y-2">
            <Label for="buscar-icono" class="text-sm font-medium">Buscar icono</Label>
            <Input
                id="buscar-icono"
                v-model="busqueda"
                type="text"
                placeholder="Ej: trabajo, hogar, música..."
                class="text-sm"
            />
        </div>

        <!-- Grid de iconos -->
        <div class="space-y-2">
            <Label class="text-sm font-medium block">
                Iconos disponibles ({{ iconosFiltrados.length }})
            </Label>
            <div
                v-if="iconosFiltrados.length > 0"
                class="grid grid-cols-6 gap-2 max-h-64 overflow-y-auto border rounded-lg p-2"
            >
                <button
                    v-for="icono in iconosFiltrados"
                    :key="icono.valor"
                    type="button"
                    class="group relative flex h-12 w-full items-center justify-center rounded-md border-2 transition-all hover:scale-110 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
                    :class="[
                        esIconoActivo(icono)
                            ? 'border-gray-900 bg-gray-100 scale-110'
                            : 'border-gray-200 hover:border-gray-400 hover:bg-gray-50',
                    ]"
                    :title="icono.nombre"
                    @click="seleccionarIcono(icono)"
                >
                    <component
                        :is="icono.componente"
                        class="h-6 w-6 transition-colors"
                        :class="esIconoActivo(icono) ? 'text-gray-900' : 'text-gray-600 group-hover:text-gray-900'"
                    />

                    <!-- Checkmark cuando está seleccionado -->
                    <span
                        v-if="esIconoActivo(icono)"
                        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-gray-900"
                    >
                        <svg
                            class="h-3 w-3 text-white"
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
                    </span>
                </button>
            </div>
            <div v-else class="text-center py-4 text-gray-500 text-sm border rounded-md bg-gray-50">
                No se encontraron iconos
            </div>
        </div>
    </div>
</template>
