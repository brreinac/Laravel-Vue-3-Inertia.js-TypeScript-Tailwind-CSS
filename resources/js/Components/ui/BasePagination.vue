<script setup lang="ts">
import BaseButton from './BaseButton.vue';
import type { PaginationMeta } from '../../types';

defineProps<{ meta: PaginationMeta }>();
const emit = defineEmits<{ change: [page: number] }>();
</script>

<template>
  <nav v-if="meta.last_page > 1" class="flex items-center justify-between gap-3" aria-label="Paginación">
    <p class="muted">Mostrando {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} de {{ meta.total }}</p>
    <div class="flex gap-2">
      <BaseButton variant="secondary" :disabled="meta.current_page === 1" aria-label="Página anterior" @click="emit('change', meta.current_page - 1)">Anterior</BaseButton>
      <span class="flex items-center px-2 text-sm text-slate-600 dark:text-slate-300">{{ meta.current_page }} / {{ meta.last_page }}</span>
      <BaseButton variant="secondary" :disabled="meta.current_page === meta.last_page" aria-label="Página siguiente" @click="emit('change', meta.current_page + 1)">Siguiente</BaseButton>
    </div>
  </nav>
</template>
