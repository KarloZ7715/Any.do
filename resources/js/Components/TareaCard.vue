<script setup>
import { Checkbox } from "@/Components/ui/checkbox";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import {
    Calendar,
    Pencil,
    Trash2,
    Tag,
    Clock,
    AlertCircle,
} from "lucide-vue-next";

const props = defineProps({
    tarea: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["toggle", "edit", "delete"]);

const handleToggle = () => {
    if (!props.loading) {
        emit("toggle", props.tarea.id);
    }
};

const handleEdit = () => {
    if (!props.loading) {
        emit("edit", props.tarea);
    }
};

const handleDelete = () => {
    if (!props.loading) {
        emit("delete", props.tarea.id);
    }
};

// Clases de prioridad
const prioridadClasses = {
    red: "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300",
    yellow: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300",
    green: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300",
};

// Clases de estado
const estadoClasses = {
    pendiente: "border-l-4 border-l-blue-500",
    completada: "border-l-4 border-l-green-500 opacity-75",
};
</script>

<template>
    <Card
        :class="[
            estadoClasses[tarea.estado],
            { 'pointer-events-none opacity-50': loading },
        ]"
    >
        <CardHeader class="pb-3">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <Checkbox
                        :checked="tarea.esta_completada"
                        :disabled="loading"
                        @update:checked="handleToggle"
                        class="mt-1"
                    />

                    <div class="flex-1 min-w-0">
                        <CardTitle
                            :class="[
                                'text-lg',
                                {
                                    'line-through text-muted-foreground':
                                        tarea.esta_completada,
                                },
                            ]"
                        >
                            {{ tarea.titulo }}
                        </CardTitle>

                        <CardDescription
                            v-if="tarea.descripcion"
                            class="mt-1 line-clamp-2"
                        >
                            {{ tarea.descripcion }}
                        </CardDescription>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        :disabled="loading"
                        @click="handleEdit"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon"
                        :disabled="loading"
                        @click="handleDelete"
                    >
                        <Trash2 class="h-4 w-4 text-red-500" />
                    </Button>
                </div>
            </div>
        </CardHeader>

        <CardContent class="pb-3">
            <div class="flex flex-wrap gap-2">
                <!-- Badge de prioridad -->
                <Badge
                    :class="prioridadClasses[tarea.prioridad_color]"
                    variant="secondary"
                >
                    {{ tarea.prioridad_texto }}
                </Badge>

                <!-- Badge de estado -->
                <Badge variant="outline">
                    {{ tarea.estado_texto }}
                </Badge>

                <!-- Badge de categoría -->
                <Badge
                    v-if="tarea.categoria"
                    variant="outline"
                    class="flex items-center gap-1"
                >
                    <Tag class="h-3 w-3" />
                    {{ tarea.categoria.nombre }}
                </Badge>
            </div>
        </CardContent>

        <CardFooter v-if="tarea.fecha_vencimiento" class="pt-0 pb-3">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <!-- Icono de fecha vencida -->
                <AlertCircle
                    v-if="tarea.esta_vencida && !tarea.esta_completada"
                    class="h-4 w-4 text-red-500"
                />
                <Calendar v-else class="h-4 w-4" />

                <span
                    :class="{
                        'text-red-500 font-medium':
                            tarea.esta_vencida && !tarea.esta_completada,
                    }"
                >
                    {{ tarea.fecha_vencimiento_humana }}
                </span>

                <!-- Días hasta vencimiento -->
                <Badge
                    v-if="
                        !tarea.esta_completada &&
                        tarea.dias_hasta_vencimiento !== null
                    "
                    variant="outline"
                    size="sm"
                    :class="{
                        'border-red-500 text-red-500': tarea.esta_vencida,
                        'border-yellow-500 text-yellow-600':
                            tarea.dias_hasta_vencimiento <= 3 &&
                            !tarea.esta_vencida,
                    }"
                >
                    <Clock class="h-3 w-3 mr-1" />
                    {{ tarea.dias_hasta_vencimiento }} días
                </Badge>
            </div>
        </CardFooter>
    </Card>
</template>
