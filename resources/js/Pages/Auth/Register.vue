<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout>

        <Head title="Registro" />

        <!-- Encabezado -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-semibold text-foreground">
                Crea tu cuenta
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Comienza a organizar tus tareas hoy
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div>
                <InputLabel for="name" value="Nombre Completo" />

                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                    autocomplete="name" placeholder="Juan Pérez" />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Correo Electrónico" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                    autocomplete="username" placeholder="tu@email.com" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Contraseña" />

                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="new-password" placeholder="Mínimo 8 caracteres" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Password Confirmation -->
            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />

                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                    v-model="form.password_confirmation" required autocomplete="new-password"
                    placeholder="Repite tu contraseña" />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit Button -->
            <div>
                <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Crear Cuenta
                </PrimaryButton>
            </div>

            <!-- Login Link -->
            <div class="text-center text-sm">
                <span class="text-muted-foreground">¿Ya tienes una cuenta?</span>
                <Link :href="route('login')"
                    class="ms-1 text-primary hover:text-primary/90 font-medium underline focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 rounded transition-colors">
                    Inicia Sesión
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
