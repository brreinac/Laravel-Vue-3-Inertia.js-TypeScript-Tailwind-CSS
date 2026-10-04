<script setup lang="ts">
withDefaults(defineProps<{
  id: string;
  label: string;
  modelValue: string | number | null;
  type?: 'text' | 'email' | 'password' | 'date' | 'search';
  placeholder?: string;
  error?: string;
  disabled?: boolean;
  required?: boolean;
}>(), {
  type: 'text',
  placeholder: '',
  error: '',
  disabled: false,
  required: false,
});

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const update = (event: Event): void => emit('update:modelValue', (event.target as HTMLInputElement).value);
</script>

<template>
  <div class="space-y-1.5">
    <label :for="id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
      {{ label }}<span v-if="required" aria-hidden="true"> *</span>
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue ?? ''"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :aria-invalid="Boolean(error)"
      :aria-describedby="error ? `${id}-error` : undefined"
      class="block w-full rounded-lg border-slate-300 bg-white text-sm text-slate-900 placeholder:text-slate-400 disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:disabled:bg-slate-800"
      :class="{ 'border-rose-500': error }"
      @input="update"
    >
    <p v-if="error" :id="`${id}-error`" class="text-sm text-rose-600" role="alert">{{ error }}</p>
  </div>
</template>
