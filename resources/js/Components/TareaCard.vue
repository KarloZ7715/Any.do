<script setup>
import { ref } from 'vue'
import { Checkbox } from '@/Components/ui/checkbox'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card'
import {
    Calendar,
    Pencil,
    Trash2,
    Tag,
    Clock,
    AlertCircle,
} from 'lucide-vue-next'

const props = defineProps({
    tarea: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['toggle', 'edit', 'delete'])

// Estado de animación
const estaAnimando = ref(false)
const estaCompletandose = ref(false)

const handleToggle = () => {
    if (!props.loading && !estaAnimando.value) {
        // Si se está completando, activar animación
        if (!props.tarea.esta_completada) {
            estaCompletandose.value = true
            estaAnimando.value = true

            // Esperar a que termine la animación antes de emitir
            setTimeout(() => {
                emit('toggle', props.tarea.id)
                estaAnimando.value = false
                // Resetear estado después de un delay adicional
                setTimeout(() => {
                    estaCompletandose.value = false
                }, 300)
            }, 600)
        } else {
            // Si se desmarca, sin animación especial
            emit('toggle', props.tarea.id)
        }
    }
}

const handleEdit = () => {
    if (!props.loading) {
        emit('edit', props.tarea)
    }
}

const handleDelete = () => {
    if (!props.loading) {
        emit('delete', props.tarea.id)
    }
}

// Clases de prioridad
const prioridadClasses = {
    red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
}

// Clases de estado
const estadoClasses = {
    pendiente: 'border-l-4 border-l-blue-500 hover:shadow-md hover:border-l-blue-600',
    completada: 'border-l-4 border-l-green-500 hover:shadow-sm',
}
</script>

<template>
    <Card :class="[
        estadoClasses[tarea.estado],
        'transition-all duration-300 ease-in-out',
        'border border-border dark:border-border',
        'hover:border-accent dark:hover:border-accent',
        'bg-task-card',
        {
            'pointer-events-none opacity-50': loading,
            'tarea-completando': estaCompletandose,
            'tarea-completada-animacion': tarea.esta_completada && !estaCompletandose,
        },
    ]">
        <CardHeader class="pb-3 px-5 pt-5">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <Checkbox :checked="tarea.esta_completada" :disabled="loading" @update:checked="handleToggle"
                        class="mt-1 border-[color:var(--task-checkbox-border)]" />

                    <div class="flex-1 min-w-0 titulo-container">
                        <CardTitle :class="[
                            'text-lg font-semibold leading-snug titulo-tarea transition-all duration-300',
                            {
                                'titulo-completado text-muted-foreground':
                                    tarea.esta_completada,
                            },
                        ]">
                            {{ tarea.titulo }}
                        </CardTitle>

                        <CardDescription v-if="tarea.descripcion" class="mt-2 line-clamp-2 text-sm leading-relaxed">
                            {{ tarea.descripcion }}
                        </CardDescription>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <Button variant="ghost" size="icon" :disabled="loading" @click="handleEdit"
                        class="hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <Pencil class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </Button>

                    <Button variant="ghost" size="icon" :disabled="loading" @click="handleDelete"
                        class="hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <Trash2 class="h-4 w-4 text-red-500 dark:text-red-400" />
                    </Button>
                </div>
            </div>
        </CardHeader>

        <CardContent class="pb-3 px-5">
            <div class="flex flex-wrap gap-2">
                <!-- Badge de prioridad -->
                <Badge :class="prioridadClasses[tarea.prioridad_color]" variant="secondary">
                    {{ tarea.prioridad_texto }}
                </Badge>

                <!-- Badge de estado -->
                <Badge variant="outline">
                    {{ tarea.estado_texto }}
                </Badge>

                <!-- Badge de categoría -->
                <Badge v-if="tarea.categoria" variant="outline" class="flex items-center gap-1">
                    <Tag class="h-3 w-3" />
                    {{ tarea.categoria.nombre }}
                </Badge>
            </div>
        </CardContent>

        <CardFooter v-if="tarea.fecha_vencimiento" class="pt-0 pb-4 px-5">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <!-- Icono de fecha vencida -->
                <AlertCircle v-if="tarea.esta_vencida && !tarea.esta_completada"
                    class="h-4 w-4 text-red-500 dark:text-red-400" />
                <Calendar v-else class="h-4 w-4" />

                <span :class="{
                    'text-red-600 dark:text-red-400 font-medium':
                        tarea.esta_vencida && !tarea.esta_completada,
                }">
                    {{ tarea.fecha_vencimiento_humana }}
                </span>

                <!-- Días hasta vencimiento -->
                <Badge v-if="
                    !tarea.esta_completada &&
                    tarea.dias_hasta_vencimiento !== null
                " variant="outline" size="sm" :class="{
                    'border-red-500 text-red-600 dark:border-red-400 dark:text-red-400 bg-red-50 dark:bg-red-900/20': tarea.esta_vencida,
                    'border-yellow-500 text-yellow-600 dark:border-yellow-400 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20':
                        tarea.dias_hasta_vencimiento <= 3 &&
                        !tarea.esta_vencida,
                }">
                    <Clock class="h-3 w-3 mr-1" />
                    {{ tarea.dias_hasta_vencimiento }} días
                </Badge>
            </div>
        </CardFooter>
    </Card>
</template>

<style scoped>
/* Animación checkmark pop */
@keyframes checkmark-pop {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.2);
    }

    100% {
        transform: scale(1);
    }
}

/* Animación strike-through */
@keyframes strike-through {
    0% {
        background-size: 0% 2px;
    }

    100% {
        background-size: 100% 2px;
    }
}

/* Animación fade out con slide */
@keyframes fade-slide {
    0% {
        opacity: 1;
        transform: translateY(0);
    }

    100% {
        opacity: 0.6;
        transform: translateY(4px);
    }
}

/* Estado: Completando (durante animación) */
.tarea-completando {
    animation: fade-slide 600ms ease-in-out forwards;
}

/* Checkbox con pop */
.tarea-completando :deep(.checkbox-root) {
    animation: checkmark-pop 200ms ease-out;
}

/* Strike-through animado en el título */
.titulo-tarea {
    position: relative;
    display: inline-block;
}

.tarea-completando .titulo-tarea,
.titulo-completado {
    background-image: linear-gradient(to right,
            currentColor 0%,
            currentColor 100%);
    background-repeat: no-repeat;
    background-position: left center;
}

.tarea-completando .titulo-tarea {
    animation: strike-through 300ms 200ms ease-out forwards;
    background-size: 0% 2px;
}

.titulo-completado {
    background-size: 100% 2px;
    text-decoration: line-through;
}

/* Estado completado (después de animación) */
.tarea-completada-animacion {
    opacity: 0.75;
}

/* Hover effect mejorado */
.Card:not(.tarea-completando):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
</style>
