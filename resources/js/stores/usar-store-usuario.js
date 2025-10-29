import { defineStore } from 'pinia'

/**
 * Store para gestión del usuario autenticado
 * Maneja el estado global del usuario actual en la aplicación
 */
export const usarStoreUsuario = defineStore('usuario', () => {
    // Estado
    const usuario = ref(null)
    const autenticado = computed(() => !!usuario.value)
    const cargando = ref(false)

    // Getters
    const nombreCompleto = computed(() => {
        if (!usuario.value) return ''
        return usuario.value.nombre || usuario.value.email
    })

    const esAdmin = computed(() => {
        return usuario.value?.rol === 'admin'
    })

    const esUsuarioRegular = computed(() => {
        return usuario.value?.rol === 'usuario'
    })

    // Actions
    function establecerUsuario(datosUsuario) {
        usuario.value = datosUsuario
    }

    function actualizarUsuario(datos) {
        if (usuario.value) {
            usuario.value = { ...usuario.value, ...datos }
        }
    }

    function cerrarSesion() {
        usuario.value = null
    }

    async function cargarUsuario() {
        cargando.value = true
        try {
            // Aquí iría la llamada a la API para obtener el usuario actual
            // const response = await axios.get('/api/usuario')
            // usuario.value = response.data
        } catch (err) {
            console.error('Error al cargar usuario:', err)
        } finally {
            cargando.value = false
        }
    }

    function reiniciar() {
        usuario.value = null
        cargando.value = false
    }

    return {
        // Estado
        usuario,
        autenticado,
        cargando,
        // Getters
        nombreCompleto,
        esAdmin,
        esUsuarioRegular,
        // Actions
        establecerUsuario,
        actualizarUsuario,
        cerrarSesion,
        cargarUsuario,
        reiniciar,
    }
})
