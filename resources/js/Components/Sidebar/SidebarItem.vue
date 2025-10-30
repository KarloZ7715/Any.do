<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usarSidebar } from '@/composables/usarSidebar';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    icono: {
        type: Object,
        required: true,
    },
    texto: {
        type: String,
        required: true,
    },
    activo: {
        type: Boolean,
        default: false,
    },
    contador: {
        type: [Number, String],
        default: null,
    },
    color: {
        type: String,
        default: null,
    },
});

const { estaColapsado } = usarSidebar();

// Clases dinámicas para el estado activo
const clasesItem = computed(() => [
    'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group',
    'hover:bg-gray-100 dark:hover:bg-gray-800',
    props.activo
        ? 'bg-gray-100 dark:bg-gray-800 font-medium text-gray-900 dark:text-white'
        : 'text-gray-700 dark:text-gray-300',
]);

// Borde de color para item activo
const estiloActivo = computed(() => {
    if (props.activo && props.color) {
        return {
            borderLeft: `3px solid ${props.color}`,
            paddingLeft: 'calc(0.75rem - 3px)',
        };
    }
    return {};
});
</script>

<template>
    <Link :href="href" :class="clasesItem" :style="estiloActivo">
        <!-- Icono -->
        <component
            :is="icono"
            :class="[
                'flex-shrink-0 transition-colors',
                activo ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400',
            ]"
            :size="20"
            :stroke-width="activo ? 2.5 : 2"
        />

        <!-- Texto (solo visible cuando expandido) -->
        <span
            v-show="!estaColapsado"
            class="flex-1 text-sm truncate transition-opacity duration-200"
        >
            {{ texto }}
        </span>

        <!-- Contador (solo visible cuando expandido) -->
        <span
            v-if="contador !== null && !estaColapsado"
            class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400"
        >
            {{ contador }}
        </span>

        <!-- Tooltip cuando colapsado -->
        <div
            v-if="estaColapsado"
            class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 pointer-events-none"
        >
            {{ texto }}
            <span v-if="contador !== null" class="ml-1 text-gray-300">({{ contador }})</span>
        </div>
    </Link>
</template>
