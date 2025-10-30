import { toast } from 'vue-sonner'

/**
 * Composable para usar notificaciones toast en toda la aplicación.
 * 
 * Proporciona métodos convenientes para mostrar diferentes tipos de toasts.
 * 
 * @returns Objeto con métodos para mostrar toasts
 */
export function usarToast() {
    /**
     * Mostrar toast de éxito.
     */
    const exito = (mensaje, opciones = {}) => {
        return toast.success(mensaje, {
            duration: 3000,
            ...opciones,
        })
    }

    /**
     * Mostrar toast de error.
     */
    const error = (mensaje, opciones = {}) => {
        return toast.error(mensaje, {
            duration: 4000,
            ...opciones,
        })
    }

    /**
     * Mostrar toast de información.
     */
    const info = (mensaje, opciones = {}) => {
        return toast.info(mensaje, {
            duration: 3000,
            ...opciones,
        })
    }

    /**
     * Mostrar toast de advertencia.
     */
    const advertencia = (mensaje, opciones = {}) => {
        return toast.warning(mensaje, {
            duration: 3500,
            ...opciones,
        })
    }

    /**
     * Mostrar toast con promesa (loading → success/error).
     */
    const promesa = (promesa, mensajes) => {
        return toast.promise(promesa, {
            loading: mensajes.cargando || 'Cargando...',
            success: mensajes.exito || 'Operación completada',
            error: mensajes.error || 'Ocurrió un error',
        })
    }

    /**
     * Toast custom con acción.
     */
    const conAccion = (mensaje, { textoAccion, onAccion, ...opciones }) => {
        return toast(mensaje, {
            action: {
                label: textoAccion,
                onClick: onAccion,
            },
            ...opciones,
        })
    }

    /**
     * Cerrar todos los toasts.
     */
    const cerrarTodos = () => {
        toast.dismiss()
    }

    return {
        exito,
        error,
        info,
        advertencia,
        promesa,
        conAccion,
        cerrarTodos,
    }
}
