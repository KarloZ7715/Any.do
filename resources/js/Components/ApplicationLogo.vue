<template>
    <svg
        viewBox="0 0 100 100"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="transition-colors"
    >
        <!-- Fondo del cuadrado con gradiente -->
        <rect
            x="10"
            y="10"
            width="80"
            height="80"
            rx="16"
            class="fill-indigo-600 dark:fill-indigo-500"
        />
        
        <!-- Sombra interior para profundidad -->
        <rect
            x="10"
            y="10"
            width="80"
            height="80"
            rx="16"
            class="fill-black/10"
        />
        
        <!-- Checkmark grande y visible -->
        <path
            d="M 30 52 L 42 64 L 70 36"
            stroke="white"
            stroke-width="8"
            stroke-linecap="round"
            stroke-linejoin="round"
            fill="none"
        />
        
        <!-- Borde exterior para definición -->
        <rect
            x="10"
            y="10"
            width="80"
            height="80"
            rx="16"
            class="stroke-indigo-700 dark:stroke-indigo-400 fill-none"
            stroke-width="2"
        />
    </svg>
</template>
