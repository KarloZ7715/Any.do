<script setup>
import { ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { usarSidebar } from '@/composables/usarSidebar';

const props = defineProps({
    titulo: {
        type: String,
        required: true,
    },
    colapsable: {
        type: Boolean,
        default: true,
    },
    colapsadoInicial: {
        type: Boolean,
        default: false,
    },
});

const { estaColapsado: sidebarColapsado } = usarSidebar();
const seccionExpandida = ref(!props.colapsadoInicial);

const alternarSeccion = () => {
    if (props.colapsable && !sidebarColapsado.value) {
        seccionExpandida.value = !seccionExpandida.value;
    }
};
</script>

<template>
    <div class="mb-6">
        <!-- Header de la sección -->
        <button
            v-if="!sidebarColapsado"
            @click="alternarSeccion"
            :class="[
                'w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider transition-colors',
                'text-gray-500 dark:text-gray-400',
                colapsable ? 'hover:text-gray-700 dark:hover:text-gray-200 cursor-pointer' : 'cursor-default',
            ]"
        >
            <span>{{ titulo }}</span>
            <ChevronDown
                v-if="colapsable"
                :size="16"
                :class="[
                    'transition-transform duration-200',
                    seccionExpandida ? 'rotate-0' : '-rotate-90',
                ]"
            />
        </button>

        <!-- Separador visual cuando sidebar colapsado -->
        <div
            v-if="sidebarColapsado"
            class="h-px bg-gray-200 dark:bg-gray-700 mx-2 mb-3"
        />

        <!-- Contenido de la sección -->
        <div
            v-show="seccionExpandida || sidebarColapsado"
            class="space-y-1"
        >
            <slot />
        </div>
    </div>
</template>
