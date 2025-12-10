<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usarSidebar } from '@/composables/usarSidebar'

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
})

const { estaColapsado } = usarSidebar()

// Clases dinámicas para el estado activo
const clasesItem = computed(() => [
    'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group',
    'hover:bg-sidebar-accent dark:hover:bg-sidebar-accent',
    props.activo
        ? 'bg-sidebar-accent dark:bg-sidebar-accent font-medium text-sidebar-foreground dark:text-sidebar-foreground'
        : 'text-sidebar-foreground/80 dark:text-sidebar-foreground/80',
])

// Borde de color para item activo
const estiloActivo = computed(() => {
    if (props.activo && props.color) {
        return {
            borderLeft: `3px solid ${props.color}`,
            paddingLeft: 'calc(0.75rem - 3px)',
        }
    }
    return {}
})
</script>

<template>
    <Link :href="href" :class="clasesItem" :style="estiloActivo" class="relative">
        <!-- Icono -->
        <component :is="icono" :class="[
            'shrink-0 transition-colors duration-200',
            activo ? 'text-sidebar-foreground dark:text-sidebar-foreground' : 'text-sidebar-foreground/60 dark:text-sidebar-foreground/60',
        ]" :size="20" :stroke-width="activo ? 2.5 : 2" />

        <!-- Texto -->
        <span class="text-sm whitespace-nowrap transition-opacity duration-200"
            :class="estaColapsado ? 'opacity-0' : 'opacity-100 delay-100'">
            {{ texto }}
        </span>

        <!-- Contador -->
        <span v-if="contador !== null"
            class="text-xs px-2 py-0.5 rounded-full bg-sidebar-border dark:bg-sidebar-border text-sidebar-foreground dark:text-sidebar-foreground whitespace-nowrap transition-opacity duration-200"
            :class="estaColapsado ? 'opacity-0' : 'opacity-100 delay-100'">
            {{ contador }}
        </span>

        <!-- Tooltip cuando colapsado -->
        <div v-if="estaColapsado"
            class="absolute left-full ml-2 px-3 py-1.5 bg-foreground text-background text-sm rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 pointer-events-none shadow-lg">
            {{ texto }}
            <span v-if="contador !== null" class="ml-1 opacity-70">({{ contador }})</span>
        </div>
    </Link>
</template>
