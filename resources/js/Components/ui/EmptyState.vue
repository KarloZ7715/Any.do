<script setup>
/**
 * Componente reutilizable para estados vacíos.
 * Muestra: icono + mensaje principal + mensaje secundario + acción opcional
 */

defineProps({
    /**
     * Mensaje principal (título)
     */
    mensaje: {
        type: String,
        required: true,
    },
    /**
     * Mensaje secundario (descripción)
     */
    descripcion: {
        type: String,
        default: null,
    },
})

const emit = defineEmits(['accion'])
</script>

<template>
    <div class="h-full flex flex-col items-center justify-center p-8">
        <!-- Contenedor del icono -->
        <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
            <slot name="icono">
                <!-- Icono por defecto (slot vacío) -->
            </slot>
        </div>

        <!-- Mensaje principal -->
        <p class="text-sm font-medium text-foreground mb-1">
            {{ mensaje }}
        </p>

        <!-- Descripción -->
        <p v-if="descripcion" class="text-xs text-muted-foreground text-center max-w-xs mb-3">
            {{ descripcion }}
        </p>

        <!-- Slot para acción (botón, link, etc.) -->
        <slot name="accion" />
    </div>
</template>
