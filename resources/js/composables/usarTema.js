/**
 * Composable para gestionar el tema (Light/Dark Mode)
 * 
 * Funcionalidades:
 * - Toggle entre light y dark mode
 * - Persistencia en localStorage
 * - Transición suave de 300ms
 * - Sincronización con atributo data-theme en <html>
 */

import { ref, watch, onMounted, computed, readonly } from 'vue'

const STORAGE_KEY = 'anydo-theme'
const TEMAS_VALIDOS = ['light', 'dark']
const TEMA_POR_DEFECTO = 'light'

// Estado reactivo compartido (singleton)
const temaActual = ref(TEMA_POR_DEFECTO)

// Variable para saber si ya se inicializó
let inicializado = false

/**
 * Obtiene el tema guardado en localStorage o el tema por defecto
 * @returns {string} 'light' | 'dark'
 */
function obtenerTemaDesdStorage() {
  if (typeof window === 'undefined') return TEMA_POR_DEFECTO

  try {
    const temaGuardado = window.localStorage.getItem(STORAGE_KEY)
    
    if (temaGuardado && TEMAS_VALIDOS.includes(temaGuardado)) {
      return temaGuardado
    }
    
    return TEMA_POR_DEFECTO
  }
  catch (error) {
    console.warn('Error al obtener tema de localStorage:', error)
    return TEMA_POR_DEFECTO
  }
}

/**
 * Guarda el tema en localStorage
 * @param {string} tema - 'light' | 'dark'
 */
function guardarTemaEnStorage(tema) {
  if (typeof window === 'undefined') return

  try {
    window.localStorage.setItem(STORAGE_KEY, tema)
  }
  catch (error) {
    console.warn('Error al guardar tema en localStorage:', error)
  }
}

/**
 * Aplica el tema al elemento <html>
 * @param {string} tema - 'light' | 'dark'
 */
function aplicarTema(tema) {
  if (typeof document === 'undefined') return

  const html = document.documentElement
  
  // Agregar clase .dark para Tailwind
  if (tema === 'dark') {
    html.classList.add('dark')
  }
  else {
    html.classList.remove('dark')
  }
  
  // También agregar data-theme por si acaso
  html.setAttribute('data-theme', tema)
}

/**
 * Inicializa el tema desde localStorage INMEDIATAMENTE
 * Se ejecuta una sola vez al cargar el módulo
 */
function inicializarTemaInmediato() {
  if (inicializado || typeof window === 'undefined') return
  
  const temaGuardado = obtenerTemaDesdStorage()
  temaActual.value = temaGuardado
  aplicarTema(temaGuardado)
  inicializado = true
}

// Ejecutar inicialización inmediata
inicializarTemaInmediato()

/**
 * Composable principal para gestionar el tema
 * @returns {Object} API del composable
 */
export function usarTema() {
  // Re-inicializar en mounted por si no se ejecutó antes
  onMounted(() => {
    if (!inicializado) {
      inicializarTemaInmediato()
    }
  })
  
  // Watch para sincronizar cambios de tema
  watch(temaActual, (nuevoTema) => {
    aplicarTema(nuevoTema)
    guardarTemaEnStorage(nuevoTema)
  })
  
  /**
   * Cambia entre light y dark mode (toggle)
   */
  function toggleTema() {
    temaActual.value = temaActual.value === 'light' ? 'dark' : 'light'
  }
  
  /**
   * Establece un tema específico
   * @param {string} tema - 'light' | 'dark'
   */
  function setTema(tema) {
    if (!TEMAS_VALIDOS.includes(tema)) {
      console.warn(`Tema inválido: ${tema}. Usando tema por defecto.`)
      tema = TEMA_POR_DEFECTO
    }
    temaActual.value = tema
  }
  
  /**
   * Verifica si el tema actual es dark
   * @returns {boolean}
   */
  const esTemaOscuro = computed(() => temaActual.value === 'dark')
  
  /**
   * Verifica si el tema actual es light
   * @returns {boolean}
   */
  const esTemaClaro = computed(() => temaActual.value === 'light')
  
  return {
    temaActual: readonly(temaActual),
    esTemaOscuro,
    esTemaClaro,
    toggleTema,
    setTema,
  }
}
