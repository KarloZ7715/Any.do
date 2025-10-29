/**
 * Composable de ejemplo para demostrar auto-imports
 * @returns {Object} Objeto con propiedades reactivas y funciones
 */
export function usarSaludo() {
    const nombre = ref('Usuario')
    const contador = ref(0)

    const mensaje = computed(() => {
        return `¡Hola ${nombre.value}! Has hecho clic ${contador.value} veces.`
    })

    function incrementar() {
        contador.value++
    }

    function cambiarNombre(nuevoNombre) {
        nombre.value = nuevoNombre
    }

    return {
        nombre,
        contador,
        mensaje,
        incrementar,
        cambiarNombre,
    }
}
