<script setup>
import { computed } from 'vue'
import Sidebar from '@/Components/Sidebar/Sidebar.vue'
import ToastManager from '@/Components/Toast/ToastManager.vue'
import { usarSidebar } from '@/composables/usarSidebar'

const { estaColapsado } = usarSidebar()

// Margen dinámico para el contenido principal según estado del sidebar
const margenContenido = computed(() => estaColapsado.value ? '60px' : '250px')
</script>

<template>
    <div class="flex min-h-screen bg-background dark:bg-background transition-colors duration-300">
        <!-- Sidebar Colapsable -->
        <Sidebar />

        <!-- Contenido Principal -->
        <div
            class="flex-1 transition-all duration-300 flex flex-col"
            :style="{ marginLeft: margenContenido }"
        >
            <!-- Header Opcional (para títulos de página) -->
            <header
                v-if="$slots.header"
                class="bg-sidebar dark:bg-sidebar border-b border-sidebar-border dark:border-sidebar-border transition-colors"
            >
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <slot name="header" />
                </div>
            </header>

            <!-- Contenido de la Página -->
            <main class="flex-1 overflow-hidden">
                <slot />
            </main>
        </div>

        <!-- Toast Notifications -->
        <ToastManager />
    </div>
</template>
