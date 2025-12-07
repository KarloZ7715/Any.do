<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <GuestLayout>

        <Head title="Iniciar Sesión" />

        <!-- Encabezado -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-semibold text-foreground">
                Bienvenido de nuevo
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Inicia sesión para continuar con tus tareas
            </p>
        </div>

        <div v-if="status"
            class="mb-4 text-sm font-medium text-success-600 dark:text-success-400 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg px-4 py-3">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div>
                <InputLabel for="email" value="Correo Electrónico" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" placeholder="tu@email.com" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Contraseña" />

                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="current-password" placeholder="••••••••" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-muted-foreground">
                        Recordarme
                    </span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="rounded-md text-sm text-primary hover:text-primary/90 underline focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors">
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <!-- Submit Button -->
            <div>
                <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Iniciar Sesión
                </PrimaryButton>
            </div>

            <!-- Register Link -->
            <div class="text-center text-sm">
                <span class="text-muted-foreground">¿No tienes una cuenta?</span>
                <Link :href="route('register')"
                    class="ms-1 text-primary hover:text-primary/90 font-medium underline focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 rounded transition-colors">
                    Regístrate
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
