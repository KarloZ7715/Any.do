<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0 transition-colors duration-300 bg-gray-50 dark:bg-gray-950"
    >
        <!-- Theme Toggle (esquina superior derecha) -->
        <div class="absolute top-4 right-4">
            <ThemeToggle />
        </div>

        <!-- Logo -->
        <div class="mb-8">
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 transition-colors" />
            </Link>
        </div>

        <!-- Card de Formulario -->
        <div
            class="w-full overflow-hidden px-8 py-10 shadow-xl sm:max-w-md sm:rounded-2xl transition-colors duration-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800"
        >
            <slot />
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400 transition-colors">
            <p>Any.do - Gestión de Tareas</p>
            <p class="mt-1 text-xs">© {{ new Date().getFullYear() }} - Proyecto Universitario DWII</p>
        </div>
    </div>
</template>
