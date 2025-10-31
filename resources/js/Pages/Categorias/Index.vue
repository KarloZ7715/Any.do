<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
import CategoriaCard from '@/Components/CategoriaCard.vue'
import FormularioCategoria from '@/Components/FormularioCategoria.vue'
import { Button } from '@/Components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog'
import { Plus, FolderOpen } from 'lucide-vue-next'
import { usarCategorias } from '@/composables/usarCategorias'

const props = defineProps({
    categorias: {
        type: Array,
        required: true,
    },
})

const { crearCategoria, actualizarCategoria, eliminarCategoria, procesando } = usarCategorias()

// Estado del modal
const modalAbierto = ref(false)
const categoriaSeleccionada = ref(null)

// Modo del formulario
const modoCrear = computed(() => categoriaSeleccionada.value === null)

// Funciones para manejar acciones
const abrirModalCrear = () => {
    categoriaSeleccionada.value = null
    modalAbierto.value = true
}

const abrirModalEditar = (idCategoria) => {
    categoriaSeleccionada.value = props.categorias.find((c) => c.id === idCategoria)
    modalAbierto.value = true
}

const cerrarModal = () => {
    modalAbierto.value = false
    categoriaSeleccionada.value = null
}

const manejarSubmit = (datos) => {
    if (modoCrear.value) {
        crearCategoria(datos, {
            onSuccess: () => {
                cerrarModal()
            },
        })
    } else {
        actualizarCategoria(categoriaSeleccionada.value.id, datos, {
            onSuccess: () => {
                cerrarModal()
            },
        })
    }
}

const manejarEliminar = (idCategoria) => {
    const categoria = props.categorias.find((c) => c.id === idCategoria)
    if (categoria) {
        eliminarCategoria(idCategoria, categoria)
    }
}
</script>

<template>
    <LayoutPrincipal>
        <Head title="Categorías" />

        <!-- Contenedor principal con fondo uniforme -->
        <div class="h-screen flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-950">
            <!-- Header con título minimalista y sombra sutil -->
            <div class="flex-shrink-0 px-6 pt-6 pb-4 bg-gray-50 dark:bg-gray-950">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <!-- Título con estilo consistente -->
                    <div 
                        class="inline-flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-200 group"
                    >
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors duration-200">
                            <FolderOpen :size="18" class="text-indigo-600 dark:text-indigo-400" :stroke-width="2.5" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Mis Categorías
                            </h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Organiza tus tareas
                            </p>
                        </div>
                    </div>
                    
                    <!-- Botón crear -->
                    <Button 
                        @click="abrirModalCrear" 
                        class="gap-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-700"
                    >
                        <Plus :size="18" />
                        Nueva Categoría
                    </Button>
                </div>

                <!-- Nota informativa con dark mode -->
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Nota:</strong> La categoría "Personal" está protegida
                        y no puede ser eliminada. Si eliminas una categoría que contiene
                        tareas, estas se moverán automáticamente a "Personal".
                    </p>
                </div>
            </div>

            <!-- Contenedor scrolleable con grid -->
            <div class="flex-1 overflow-y-auto px-6 pb-6 bg-gray-50 dark:bg-gray-950 scrollbar-thin">
                <!-- Grid de Categorías -->
                <div
                    v-if="categorias.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                >
                    <CategoriaCard
                        v-for="categoria in categorias"
                        :key="categoria.id"
                        :categoria="categoria"
                        @edit="abrirModalEditar"
                        @delete="manejarEliminar"
                    />
                </div>

                <!-- Estado vacío -->
                <div
                    v-else
                    class="flex items-center justify-center h-full"
                >
                    <div class="text-center py-12 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm px-8 max-w-md">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                            <FolderOpen :size="32" class="text-gray-400 dark:text-gray-600" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            No tienes categorías
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Crea tu primera categoría para organizar tus tareas
                        </p>
                        <Button 
                            @click="abrirModalCrear" 
                            class="gap-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-700"
                        >
                            <Plus :size="18" />
                            Crear Categoría
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Crear/Editar -->
        <Dialog :open="modalAbierto" @update:open="cerrarModal">
            <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {{
                            modoCrear
                                ? 'Nueva Categoría'
                                : 'Editar Categoría'
                        }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            modoCrear
                                ? 'Completa los datos para crear una nueva categoría'
                                : 'Modifica los datos de la categoría'
                        }}
                    </DialogDescription>
                </DialogHeader>
                <FormularioCategoria
                    :categoria="categoriaSeleccionada"
                    :loading="procesando"
                    @submit="manejarSubmit"
                    @cancel="cerrarModal"
                />
            </DialogContent>
        </Dialog>
    </LayoutPrincipal>
</template>

<style scoped>
/* Scrollbar personalizado */
.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.2) transparent;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.2);
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.4);
}

.dark .scrollbar-thin {
    scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.3);
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.5);
}
</style>
