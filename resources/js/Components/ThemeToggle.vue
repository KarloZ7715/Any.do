<script setup>
import { Sun, Moon } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { usarTema } from '@/composables/usarTema'

const { temaActual, esTemaOscuro, toggleTema } = usarTema()
</script>

<template>
  <Button
    variant="ghost"
    size="icon"
    @click="toggleTema"
    :aria-label="esTemaOscuro ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    class="relative h-9 w-9 rounded-md hover:bg-accent/10 transition-colors duration-200"
  >
    <!-- Icono Sol (Light Mode) -->
    <Sun
      v-show="!esTemaOscuro"
      :size="20"
      class="absolute inset-0 m-auto text-warning-600 transition-all duration-300"
      :class="{
        'rotate-0 scale-100 opacity-100': !esTemaOscuro,
        'rotate-90 scale-0 opacity-0': esTemaOscuro,
      }"
    />
    
    <!-- Icono Luna (Dark Mode) -->
    <Moon
      v-show="esTemaOscuro"
      :size="20"
      class="absolute inset-0 m-auto text-primary-400 transition-all duration-300"
      :class="{
        'rotate-0 scale-100 opacity-100': esTemaOscuro,
        '-rotate-90 scale-0 opacity-0': !esTemaOscuro,
      }"
    />
  </Button>
</template>

<style scoped>
/* Animación de rotación suave para el toggle */
.rotate-0 {
  transform: rotate(0deg);
}

.rotate-90 {
  transform: rotate(90deg);
}

.-rotate-90 {
  transform: rotate(-90deg);
}

.scale-0 {
  transform: scale(0);
}

.scale-100 {
  transform: scale(1);
}
</style>
