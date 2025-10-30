<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Menu, CheckSquare, FolderOpen, CalendarDays, CalendarRange, ListTodo } from 'lucide-vue-next';
import { usarSidebar } from '@/composables/usarSidebar';
import SidebarSection from './SidebarSection.vue';
import SidebarItem from './SidebarItem.vue';

const page = usePage();
const { estaColapsado, alternarSidebar, clasesSidebar } = usarSidebar();

// Datos del usuario autenticado
const usuario = computed(() => page.props.auth?.user);

// Categorías del usuario
const categorias = computed(() => page.props.categorias || []);

// Determinar ruta activa
const rutaActual = computed(() => route().current());

const esRutaActiva = (nombreRuta) => {
    return rutaActual.value === nombreRuta;
};
</script>

<template>
    <aside
        :class="[
            'fixed left-0 top-0 h-screen bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800',
            'transition-all duration-300 ease-in-out flex flex-col z-40',
            clasesSidebar,
        ]"
    >
        <!-- Header con Logo y Toggle -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
            <div v-show="!estaColapsado" class="flex items-center gap-2">
                <CheckSquare :size="24" class="text-indigo-600 dark:text-indigo-500" :stroke-width="2.5" />
                <span class="text-lg font-bold text-gray-900 dark:text-white">Any.do</span>
            </div>

            <!-- Botón toggle -->
            <button
                @click="alternarSidebar"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                :class="estaColapsado ? 'mx-auto' : ''"
            >
                <Menu :size="20" class="text-gray-600 dark:text-gray-400" />
            </button>
        </div>

        <!-- Contenido scrollable -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-2">
            <!-- Sección: Vistas Principales -->
            <SidebarSection titulo="Vistas Principales" :colapsable="false">
                <SidebarItem
                    :href="route('tareas.proximos-siete-dias')"
                    :icono="CalendarDays"
                    texto="Próximos 7 Días"
                    :activo="esRutaActiva('tareas.proximos-siete-dias')"
                    :contador="page.props.contadores?.tareas_proximos_7_dias"
                />

                <SidebarItem
                    :href="route('tareas.todas')"
                    :icono="ListTodo"
                    texto="Todas mis Tareas"
                    :activo="esRutaActiva('tareas.todas')"
                    :contador="page.props.contadores?.tareas_totales"
                />

                <SidebarItem
                    :href="route('tareas.calendario')"
                    :icono="CalendarRange"
                    texto="Mi Calendario"
                    :activo="esRutaActiva('tareas.calendario')"
                />
            </SidebarSection>

            <!-- Sección: Categorías -->
            <SidebarSection titulo="Categorías" :colapsable="true">
                <!-- Lista de categorías del usuario -->
                <SidebarItem
                    v-for="categoria in categorias"
                    :key="categoria.id"
                    :href="route('tareas.index', { filter: { categoria_id: categoria.id } })"
                    :icono="FolderOpen"
                    :texto="categoria.nombre"
                    :activo="false"
                    :contador="categoria.tareas_count"
                    :color="categoria.color"
                />

                <!-- Administrar categorías -->
                <SidebarItem
                    :href="route('categorias.index')"
                    :icono="FolderOpen"
                    texto="Administrar categorías"
                    :activo="esRutaActiva('categorias.index')"
                    class="mt-2 border-t border-gray-200 dark:border-gray-800 pt-2"
                />
            </SidebarSection>
        </div>

        <!-- Footer con usuario (opcional) -->
        <div
            v-if="usuario"
            class="border-t border-gray-200 dark:border-gray-800 p-4"
        >
            <div v-show="!estaColapsado" class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm">
                    {{ usuario.name.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ usuario.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ usuario.email }}
                    </p>
                </div>
            </div>

            <!-- Avatar solo cuando colapsado -->
            <div v-show="estaColapsado" class="flex justify-center">
                <div class="w-8 h-8 rounded-full bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm">
                    {{ usuario.name.charAt(0).toUpperCase() }}
                </div>
            </div>
        </div>
    </aside>
</template>
