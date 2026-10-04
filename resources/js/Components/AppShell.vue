<script setup lang="ts">
import { onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useAuthStore } from '../stores/auth';
import { useDarkMode } from '../composables/useDarkMode';
import BaseButton from './ui/BaseButton.vue';
import GlobalSearch from './GlobalSearch.vue';

const auth = useAuthStore();
const { isDark, toggle } = useDarkMode();

onMounted(async () => {
  if (!auth.initialized) await auth.loadUser();
  if (!auth.token) router.visit('/');
});

const signOut = async (): Promise<void> => {
  await auth.logout();
  router.visit('/');
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
      <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6">
        <Link href="/dashboard" class="shrink-0 text-lg font-bold text-brand-600">QVOX Tasks</Link>
        <nav class="hidden items-center gap-4 text-sm font-medium sm:flex" aria-label="Navegación principal">
          <Link href="/dashboard" class="text-slate-600 hover:text-brand-600 dark:text-slate-300">Dashboard</Link>
          <Link href="/projects" class="text-slate-600 hover:text-brand-600 dark:text-slate-300">Proyectos</Link>
          <Link href="/tasks" class="text-slate-600 hover:text-brand-600 dark:text-slate-300">Tareas</Link>
        </nav>
        <GlobalSearch class="ml-auto" />
        <BaseButton variant="ghost" :aria-label="isDark ? 'Usar modo claro' : 'Usar modo oscuro'" @click="toggle">{{ isDark ? '☀' : '☾' }}</BaseButton>
        <BaseButton variant="ghost" aria-label="Cerrar sesión" @click="signOut">Salir</BaseButton>
      </div>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
      <slot />
    </main>
  </div>
</template>
