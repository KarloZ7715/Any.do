<script setup>
import LayoutPrincipal from "@/Layouts/LayoutPrincipal.vue";
import ListaTareas from "@/Components/ListaTareas.vue";
import ModalTarea from "@/Components/ModalTarea.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Plus, Search, Filter } from "lucide-vue-next";
import { usarTareas } from "@/composables/usarTareas";
import { usarFiltros } from "@/composables/usarFiltros";

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
});

// Composables
const {
    cargando,
    procesando,
    crearTarea,
    actualizarTarea,
    eliminarTarea,
    toggleEstado,
} = usarTareas();
const {
    filtros: filtrosActivos,
    aplicarFiltros,
    limpiarFiltros,
    establecerFiltro,
} = usarFiltros(props.filtros);

// Estado del modal
const modalAbierto = ref(false);
const modalModo = ref("create");
const tareaActual = ref(null);

// Abrir modal para crear
const abrirModalCrear = () => {
    modalModo.value = "create";
    tareaActual.value = null;
    modalAbierto.value = true;
};

// Abrir modal para editar
const abrirModalEditar = (tarea) => {
    modalModo.value = "edit";
    tareaActual.value = tarea;
    modalAbierto.value = true;
};

// Cerrar modal
const cerrarModal = () => {
    modalAbierto.value = false;
    tareaActual.value = null;
};

// Manejar submit del formulario
const manejarSubmit = (form) => {
    const opciones = {
        onSuccess: () => {
            cerrarModal();
        },
    };

    if (modalModo.value === "create") {
        crearTarea(form.data(), opciones);
    } else {
        actualizarTarea(tareaActual.value.id, form.data(), opciones);
    }
};

// Manejar toggle de estado
const manejarToggle = (tarea) => {
    toggleEstado(tarea.id);
};

// Manejar edición
const manejarEditar = (tarea) => {
    abrirModalEditar(tarea);
};

// Manejar eliminación
const manejarEliminar = (tarea) => {
    eliminarTarea(tarea.id);
};

// Opciones de filtros
const opcionesEstado = [
    { value: "todas", label: "Todas" },
    { value: "pendiente", label: "Pendientes" },
    { value: "completada", label: "Completadas" },
];

const opcionesPrioridad = [
    { value: null, label: "Todas" },
    { value: 1, label: "Alta" },
    { value: 2, label: "Media" },
    { value: 3, label: "Baja" },
];

const opcionesVencimiento = [
    { value: null, label: "Todas" },
    { value: "hoy", label: "Hoy" },
    { value: "semana", label: "Esta semana" },
    { value: "vencidas", label: "Vencidas" },
];
</script>

<template>
    <LayoutPrincipal>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Mis Tareas
                    </h2>
                    <p class="text-muted-foreground">
                        Gestiona tus tareas de manera eficiente
                    </p>
                </div>
                <Button @click="abrirModalCrear">
                    <Plus class="h-4 w-4 mr-2" />
                    Nueva Tarea
                </Button>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar de filtros -->
            <aside class="lg:col-span-1">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Filter class="h-5 w-5" />
                            Filtros
                        </CardTitle>
                        <CardDescription> Filtra tus tareas </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Búsqueda -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Buscar</label>
                            <div class="relative">
                                <Search
                                    class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground"
                                />
                                <Input
                                    v-model="filtrosActivos.buscar"
                                    type="search"
                                    placeholder="Buscar tareas..."
                                    class="pl-8"
                                    @input="aplicarFiltros"
                                />
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Estado</label>
                            <Select v-model="filtrosActivos.estado">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Seleccionar estado"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opcion in opcionesEstado"
                                        :key="opcion.value"
                                        :value="opcion.value"
                                    >
                                        {{ opcion.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Prioridad -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Prioridad</label>
                            <Select v-model="filtrosActivos.prioridad">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Seleccionar prioridad"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opcion in opcionesPrioridad"
                                        :key="opcion.value"
                                        :value="opcion.value"
                                    >
                                        {{ opcion.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Categoría -->
                        <div v-if="categorias.length > 0" class="space-y-2">
                            <label class="text-sm font-medium">Categoría</label>
                            <Select v-model="filtrosActivos.categoria_id">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Todas las categorías"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">
                                        Todas
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
                        </div>

                        <!-- Vencimiento -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium"
                                >Vencimiento</label
                            >
                            <Select v-model="filtrosActivos.vencimiento">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Seleccionar vencimiento"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opcion in opcionesVencimiento"
                                        :key="opcion.value"
                                        :value="opcion.value"
                                    >
                                        {{ opcion.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Botón limpiar filtros -->
                        <Button
                            variant="outline"
                            class="w-full"
                            @click="limpiarFiltros"
                        >
                            Limpiar filtros
                        </Button>
                    </CardContent>
                </Card>
            </aside>

            <!-- Lista de tareas -->
            <main class="lg:col-span-3">
                <ListaTareas
                    :tareas="tareas.data"
                    :loading="cargando || procesando"
                    @toggle="manejarToggle"
                    @edit="manejarEditar"
                    @delete="manejarEliminar"
                />

                <!-- Paginación -->
                <div
                    v-if="tareas.links && tareas.links.length > 3"
                    class="mt-6"
                >
                    <nav class="flex items-center justify-center gap-2">
                        <Button
                            v-for="(link, index) in tareas.links"
                            :key="index"
                            variant="outline"
                            size="sm"
                            :disabled="!link.url || link.active"
                            @click="link.url && router.get(link.url)"
                        >
                            {{
                                link.label
                                    .replace("&laquo;", "«")
                                    .replace("&raquo;", "»")
                            }}
                        </Button>
                    </nav>
                </div>
            </main>
        </div>

        <!-- Modal crear/editar -->
        <ModalTarea
            :open="modalAbierto"
            :mode="modalModo"
            :tarea="tareaActual"
            :categorias="categorias"
            @close="cerrarModal"
            @submit="manejarSubmit"
        />
    </LayoutPrincipal>
</template>
