<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseButton from '../Components/ui/BaseButton.vue';
import BaseInput from '../Components/ui/BaseInput.vue';
import { ApiError } from '../lib/api';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const form = reactive({ email: '', password: '' });
const errors = reactive<Record<string, string>>({});
const feedback = ref('');

onMounted(async () => {
  await auth.loadUser();
  if (auth.isAuthenticated) router.visit('/dashboard');
});

const submit = async (): Promise<void> => {
  errors.email = '';
  errors.password = '';
  feedback.value = '';
  if (!form.email) errors.email = 'El correo electrónico es obligatorio.';
  if (!form.password) errors.password = 'La contraseña es obligatoria.';
  if (errors.email || errors.password) return;

  try {
    await auth.login(form.email, form.password);
    router.visit('/dashboard');
  } catch (exception) {
    if (exception instanceof ApiError) {
      Object.entries(exception.errors ?? {}).forEach(([field, messages]) => { errors[field] = messages[0] ?? ''; });
      feedback.value = exception.message;
    } else feedback.value = 'No fue posible iniciar sesión. Intente nuevamente.';
  }
};
</script>

<template>
  <main class="grid min-h-screen place-items-center bg-slate-50 p-4 dark:bg-slate-950">
    <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
      <p class="text-sm font-semibold text-brand-600">QVOX TASK MANAGER</p>
      <h1 class="mt-2 text-3xl font-bold tracking-tight">Bienvenido</h1>
      <p class="mt-2 text-slate-500 dark:text-slate-400">Ingresa para gestionar el trabajo de tu equipo.</p>
      <form class="mt-8 space-y-5" @submit.prevent="submit">
        <BaseInput id="email" v-model="form.email" type="email" label="Correo electrónico" autocomplete="email" required :error="errors.email" />
        <BaseInput id="password" v-model="form.password" type="password" label="Contraseña" autocomplete="current-password" required :error="errors.password" />
        <p v-if="feedback" class="rounded-lg bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/40 dark:text-rose-300" role="alert">{{ feedback }}</p>
        <BaseButton class="w-full" type="submit" :loading="auth.loading">Iniciar sesión</BaseButton>
      </form>
      <p class="mt-6 text-center text-xs text-slate-500">Demo: admin@qvox.local / password</p>
    </section>
  </main>
</template>
