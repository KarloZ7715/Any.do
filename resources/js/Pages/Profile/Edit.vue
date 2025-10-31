<script setup>
import LayoutPrincipal from '@/Layouts/LayoutPrincipal.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import { Head } from '@inertiajs/vue3'
import { User } from 'lucide-vue-next'

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})
</script>

<template>
    <Head title="Perfil" />

    <LayoutPrincipal>
        <!-- Contenedor principal con fondo uniforme -->
        <div class="h-screen flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-950">
            <!-- Header con título minimalista -->
            <div class="flex-shrink-0 px-6 pt-6 pb-4 bg-gray-50 dark:bg-gray-950">
                <div 
                    class="inline-flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-200 group"
                >
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors duration-200">
                        <User :size="18" class="text-indigo-600 dark:text-indigo-400" :stroke-width="2.5" />
                    </div>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Mi Perfil
                    </h1>
                </div>
            </div>

            <!-- Contenedor scrolleable -->
            <div class="flex-1 overflow-y-auto px-6 pb-6 bg-gray-50 dark:bg-gray-950 scrollbar-thin">
                <div class="mx-auto max-w-4xl space-y-6">
                    <!-- Información del perfil -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-200 p-6">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>

                    <!-- Actualizar contraseña -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-200 p-6">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>

                    <!-- Eliminar cuenta -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-200 p-6">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
            </div>
        </div>
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
