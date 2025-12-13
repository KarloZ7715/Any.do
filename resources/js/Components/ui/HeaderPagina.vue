<script setup>
/**
 * Componente reutilizable para headers de página.
 * Muestra: icono + título + subtítulo opcional
 *
 * Usado en TodasLasTareas, Proximos7Dias, MiCalendario, etc.
 */

defineProps({
    /**
     * Título principal
     */
    titulo: {
        type: String,
        required: true,
    },
    /**
     * Subtítulo opcional (descripción o información adicional)
     */
    subtitulo: {
        type: String,
        default: null,
    },
    /**
     * Color del icono
     * Si no se proporciona, usa el color primary
     */
    colorIcono: {
        type: String,
        default: null,
    },
})
</script>

<template>
    <div
        class="inline-flex items-center gap-3 px-4 py-3 bg-card rounded-xl border border-transparent shadow-[0_2px_16px_5px_rgba(0,0,0,0.10)] hover:shadow-[0_8px_24px_0px_rgba(0,0,0,0.15)] dark:shadow-none dark:hover:shadow-none transition-all duration-200 group"
    >
        <!-- Contenedor del icono -->
        <div
            class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors duration-200"
            :class="colorIcono ? '' : 'bg-primary/10 group-hover:bg-primary/20'"
            :style="colorIcono ? { backgroundColor: colorIcono + '20' } : {}"
        >
            <slot name="icono">
                <!-- Icono por defecto (slot vacío) -->
            </slot>
        </div>

        <!-- Texto -->
        <div>
            <h1 class="text-lg font-semibold text-foreground">
                {{ titulo }}
            </h1>
            <p v-if="subtitulo" class="text-xs text-muted-foreground">
                {{ subtitulo }}
            </p>
            <!-- Slot para contenido adicional (ej: botón "Ver todas") -->
            <slot name="extra" />
        </div>
    </div>
</template>
