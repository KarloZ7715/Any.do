<script setup>
import { ListTodo, Search, Filter } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuickAddInput from '@/Components/QuickAddInput.vue'
import TareaCard from '@/Components/TareaCard.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/Components/ui/button'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select'

const props = defineProps({
    tareas: {
        type: Object,
        required: true,
    },
    filtros: {
        type: Object,
        default: () => ({}),
    },
    categorias: {
        type: Array,
        default: () => [],
    },
})

// Filtros reactivos
const busqueda = ref(props.filtros.buscar || '')
const estadoSeleccionado = ref(props.filtros.estado || 'todas')
const prioridadSeleccionada = ref(props.filtros.prioridad || 'todas')
const categoriaSeleccionada = ref(props.filtros.categoria_id || 'todas')
const ordenSeleccionado = ref(props.filtros.ordenar || 'orden')

// Aplicar filtros
const aplicarFiltros = () => {
    router.get(
        route('tareas.todas'),
        {
            buscar: busqueda.value || undefined,
            estado: estadoSeleccionado.value !== 'todas' ? estadoSeleccionado.value : undefined,
            prioridad: prioridadSeleccionada.value !== 'todas' ? prioridadSeleccionada.value : undefined,
            categoria_id: categoriaSeleccionada.value !== 'todas' ? categoriaSeleccionada.value : undefined,
            ordenar: ordenSeleccionado.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

// Limpiar filtros
const limpiarFiltros = () => {
    busqueda.value = ''
    estadoSeleccionado.value = 'todas'
    prioridadSeleccionada.value = 'todas'
    categoriaSeleccionada.value = 'todas'
    ordenSeleccionado.value = 'orden'
    aplicarFiltros()
}

// Contador de filtros activos
const filtrosActivos = computed(() => {
    let count = 0
    if (busqueda.value) count++
    if (estadoSeleccionado.value !== 'todas') count++
    if (prioridadSeleccionada.value !== 'todas') count++
    if (categoriaSeleccionada.value !== 'todas') count++
    return count
})

// Estadísticas
const estadisticas = computed(() => {
    const pendientes = props.tareas.data.filter((t) => t.estado === 'pendiente').length
    const completadas = props.tareas.data.filter((t) => t.estado === 'completada').length
    return { pendientes, completadas, total: props.tareas.meta.total }
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                            <ListTodo :size="24" class="text-indigo-600 dark:text-indigo-400" :stroke-width="2" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Todas mis Tareas
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ estadisticas.total }} tareas en total • {{ estadisticas.pendientes }} pendientes • {{ estadisticas.completadas }} completadas
                            </p>
                        </div>
                    </div>

                    <!-- Quick Add Input -->
                    <div class="mt-6">
                        <QuickAddInput :categorias="categorias" />
                    </div>

                    <!-- Filtros -->
                    <div class="mt-6 space-y-4">
                        <!-- Búsqueda -->
                        <div class="relative">
                            <Search :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                            <Input
                                v-model="busqueda"
                                type="text"
                                placeholder="Buscar tareas..."
                                class="pl-10"
                                @keydown.enter="aplicarFiltros"
                            />
                        </div>

                        <!-- Filtros avanzados -->
                        <div class="flex flex-wrap gap-3">
                            <Select v-model="estadoSeleccionado" @update:model-value="aplicarFiltros">
                                <SelectTrigger class="w-[140px]">
                                    <SelectValue placeholder="Estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="todas">Todas</SelectItem>
                                    <SelectItem value="pendiente">Pendientes</SelectItem>
                                    <SelectItem value="completada">Completadas</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="prioridadSeleccionada" @update:model-value="aplicarFiltros">
                                <SelectTrigger class="w-[140px]">
                                    <SelectValue placeholder="Prioridad" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="todas">Todas</SelectItem>
                                    <SelectItem value="1">🔴 Alta</SelectItem>
                                    <SelectItem value="2">🟡 Media</SelectItem>
                                    <SelectItem value="3">🟢 Baja</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="categoriaSeleccionada" @update:model-value="aplicarFiltros">
                                <SelectTrigger class="w-[160px]">
                                    <SelectValue placeholder="Categoría" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="todas">Todas</SelectItem>
                                    <SelectItem
                                        v-for="categoria in categorias"
                                        :key="categoria.id"
                                        :value="String(categoria.id)"
                                    >
                                        {{ categoria.nombre }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="ordenSeleccionado" @update:model-value="aplicarFiltros">
                                <SelectTrigger class="w-[160px]">
                                    <SelectValue placeholder="Ordenar por" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="orden">Orden personalizado</SelectItem>
                                    <SelectItem value="prioridad">Prioridad</SelectItem>
                                    <SelectItem value="fecha_vencimiento">Fecha vencimiento</SelectItem>
                                    <SelectItem value="created_at">Fecha creación</SelectItem>
                                </SelectContent>
                            </Select>

                            <Button
                                v-if="filtrosActivos > 0"
                                variant="outline"
                                size="sm"
                                @click="limpiarFiltros"
                            >
                                Limpiar filtros ({{ filtrosActivos }})
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Lista de tareas -->
                <div v-if="tareas.data.length > 0" class="space-y-2">
                    <TareaCard
                        v-for="tarea in tareas.data"
                        :key="tarea.id"
                        :tarea="tarea"
                    />

                    <!-- Paginación simple -->
                    <div v-if="tareas.meta.last_page > 1" class="flex items-center justify-center gap-2 mt-8">
                        <Button
                            v-for="link in tareas.links"
                            :key="link.label"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url)"
                            v-html="link.label"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                        <ListTodo :size="48" class="text-gray-400 dark:text-gray-600" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No se encontraron tareas
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 max-w-sm">
                        {{ filtrosActivos > 0 ? 'Intenta ajustar los filtros para ver más resultados.' : 'Aún no has creado ninguna tarea. Comienza agregando una nueva tarea arriba.' }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
