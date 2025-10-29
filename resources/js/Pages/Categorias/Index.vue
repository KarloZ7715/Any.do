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

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Mis Categorías
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Organiza tus tareas con categorías personalizadas
                        </p>
                    </div>
                    <Button @click="abrirModalCrear" class="gap-2">
                        <Plus :size="18" />
                        Nueva Categoría
                    </Button>
                </div>

                <!-- Nota informativa -->
                <div
                    class="rounded-lg bg-blue-50 border border-blue-200 p-4 mb-6"
                >
                    <p class="text-sm text-blue-800">
                        <strong>Nota:</strong> La categoría "Personal" está protegida
                        y no puede ser eliminada. Si eliminas una categoría que contiene
                        tareas, estas se moverán automáticamente a "Personal".
                    </p>
                </div>

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
                    class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300"
                >
                    <FolderOpen
                        :size="48"
                        class="mx-auto text-gray-400 mb-3"
                    />
                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        No tienes categorías
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Crea tu primera categoría para organizar tus tareas
                    </p>
                    <Button @click="abrirModalCrear" class="gap-2">
                        <Plus :size="18" />
                        Crear Categoría
                    </Button>
                </div>
            </div>
        </div>

        <!-- Modal para Crear/Editar -->
        <Dialog :open="modalAbierto" @update:open="cerrarModal">
            <DialogContent class="max-w-md">
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
