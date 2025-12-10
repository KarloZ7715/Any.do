<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { Pin, CheckSquare, FolderOpen, CalendarDays, CalendarRange, ListTodo, User, LogOut, Sun, Moon } from 'lucide-vue-next'
import { usarSidebar } from '@/composables/usarSidebar'
import { usarTema } from '@/composables/usarTema'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import SidebarSection from './SidebarSection.vue'
import SidebarItem from './SidebarItem.vue'

const page = usePage()
const { estaColapsado, estaFijado, alternarFijado, handleMouseEnter, handleMouseLeave, clasesSidebar } = usarSidebar()
const { temaActual, toggleTema } = usarTema()

// Datos del usuario autenticado
const usuario = computed(() => page.props.auth?.user)

// Categorías del usuario
const categorias = computed(() => page.props.categorias || [])

// Determinar ruta activa
const rutaActual = computed(() => route().current())

const esRutaActiva = (nombreRuta) => {
    return rutaActual.value === nombreRuta
}

// Handler para cerrar sesión
const cerrarSesion = () => {
    router.post(route('logout'))
}
</script>

<template>
    <aside :class="[
        'fixed left-0 top-0 h-screen bg-sidebar dark:bg-sidebar border-r border-sidebar-border dark:border-sidebar-border',
        'transition-all duration-300 ease-in-out flex flex-col z-40 overflow-hidden',
        clasesSidebar,
    ]" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
        <!-- Header con Logo y Pin -->
        <div class="flex items-center p-4 border-b border-sidebar-border dark:border-sidebar-border gap-2">
            <!-- Logo/Menu (siempre visible) -->
            <Popover>
                <PopoverTrigger as-child>
                    <button
                        class="p-1.5 rounded-lg hover:bg-sidebar-accent dark:hover:bg-sidebar-accent transition-colors shrink-0">
                        <CheckSquare :size="24" class="text-indigo-600 dark:text-indigo-500" :stroke-width="2.5" />
                    </button>
                </PopoverTrigger>
                <PopoverContent class="w-48 p-2" align="start">
                    <div class="flex flex-col gap-1">
                        <!-- Perfil -->
                        <a :href="route('profile.edit')"
                            class="flex items-center gap-3 px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent dark:hover:bg-sidebar-accent text-sidebar-foreground dark:text-sidebar-foreground transition-colors">
                            <User :size="16" />
                            <span>Perfil</span>
                        </a>

                        <!-- Dark/Light Mode Toggle -->
                        <button @click="toggleTema"
                            class="flex items-center gap-3 px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent dark:hover:bg-sidebar-accent text-sidebar-foreground dark:text-sidebar-foreground transition-colors w-full text-left">
                            <Sun v-if="temaActual === 'dark'" :size="16" />
                            <Moon v-else :size="16" />
                            <span>{{ temaActual === 'dark' ? 'Modo Claro' : 'Modo Oscuro' }}</span>
                        </button>

                        <!-- Cerrar Sesión -->
                        <button @click="cerrarSesion"
                            class="flex items-center gap-3 px-3 py-2 text-sm rounded-md hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 dark:text-red-400 transition-colors w-full text-left">
                            <LogOut :size="16" />
                            <span>Cerrar Sesión</span>
                        </button>
                    </div>
                </PopoverContent>
            </Popover>

            <!-- Nombre de usuario -->
            <div class="flex flex-col min-w-0 transition-opacity duration-200 whitespace-nowrap"
                :class="estaColapsado ? 'opacity-0' : 'opacity-100 delay-100'">
                <span class="text-lg font-bold text-sidebar-foreground dark:text-sidebar-foreground truncate">
                    {{ usuario?.name || 'Usuario' }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Any.do</span>
            </div>

            <!-- Spacer flexible -->
            <div class="flex-1"></div>

            <!-- Botón Pin (fade) -->
            <button @click="alternarFijado"
                class="p-2 rounded-lg hover:bg-sidebar-accent dark:hover:bg-sidebar-accent transition-all duration-200 shrink-0"
                :class="estaColapsado ? 'opacity-0 pointer-events-none' : 'opacity-100 delay-100'"
                :title="estaFijado ? 'Desfijar sidebar' : 'Fijar sidebar'">
                <Pin :size="20" :class="[
                    'transition-colors',
                    estaFijado
                        ? 'text-indigo-600 dark:text-indigo-500'
                        : 'text-gray-400 dark:text-gray-600'
                ]" />
            </button>
        </div>

        <!-- Contenido scrollable -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-2">
            <!-- Sección: Vistas Principales -->
            <SidebarSection titulo="Vistas Principales" :colapsable="false">
                <SidebarItem :href="route('tareas.proximos-siete-dias')" :icono="CalendarDays" texto="Próximos 7 Días"
                    :activo="esRutaActiva('tareas.proximos-siete-dias')"
                    :contador="page.props.contadores?.tareas_proximos_7_dias" />

                <SidebarItem :href="route('tareas.todas')" :icono="ListTodo" texto="Todas mis Tareas"
                    :activo="esRutaActiva('tareas.todas')" :contador="page.props.contadores?.tareas_totales" />

                <SidebarItem :href="route('tareas.calendario')" :icono="CalendarRange" texto="Mi Calendario"
                    :activo="esRutaActiva('tareas.calendario')" />
            </SidebarSection>

            <!-- Sección: Categorías -->
            <SidebarSection titulo="Categorías" :colapsable="true">
                <!-- Lista de categorías del usuario -->
                <SidebarItem v-for="categoria in categorias" :key="categoria.id"
                    :href="route('tareas.todas', { categoria_id: categoria.id })" :icono="FolderOpen"
                    :texto="categoria.nombre"
                    :activo="esRutaActiva('tareas.todas') && $page.props.categoriaSeleccionada?.id === categoria.id"
                    :contador="categoria.tareas_count" :color="categoria.color" />

                <!-- Administrar categorías -->
                <SidebarItem :href="route('categorias.index')" :icono="FolderOpen" texto="Administrar categorías"
                    :activo="esRutaActiva('categorias.index')"
                    class="mt-2 border-t border-gray-200 dark:border-gray-800 pt-2" />
            </SidebarSection>
        </div>

        <!-- Footer con usuario -->
        <div v-if="usuario" class="border-t border-sidebar-border dark:border-sidebar-border p-4">
            <div class="flex items-center gap-3">
                <!-- Avatar -->
                <div
                    class="w-8 h-8 rounded-full bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                    {{ usuario.name.charAt(0).toUpperCase() }}
                </div>
                <!-- Info -->
                <div class="flex-1 min-w-0 transition-opacity duration-200 whitespace-nowrap"
                    :class="estaColapsado ? 'opacity-0' : 'opacity-100 delay-100'">
                    <p class="text-sm font-medium text-sidebar-foreground dark:text-sidebar-foreground truncate">
                        {{ usuario.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ usuario.email }}
                    </p>
                </div>
            </div>
        </div>
    </aside>
</template>
