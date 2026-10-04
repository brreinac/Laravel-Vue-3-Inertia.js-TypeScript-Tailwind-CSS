<script setup lang="ts">
export interface SelectOption {
  value: string | number;
  label: string;
}

withDefaults(defineProps<{
  id: string;
  label: string;
  modelValue: string | number | null;
  options: SelectOption[];
  placeholder?: string;
  error?: string;
  disabled?: boolean;
  required?: boolean;
}>(), {
  placeholder: 'Seleccione una opción',
  error: '',
  disabled: false,
  required: false,
});

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<template>
  <div class="space-y-1.5">
    <label :for="id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
      {{ label }}<span v-if="required" aria-hidden="true"> *</span>
    </label>
    <select
      :id="id"
      :value="modelValue ?? ''"
      :disabled="disabled"
      :required="required"
      :aria-invalid="Boolean(error)"
      :aria-describedby="error ? `${id}-error` : undefined"
      class="block w-full rounded-lg border-slate-300 bg-white text-sm text-slate-900 disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:disabled:bg-slate-800"
      :class="{ 'border-rose-500': error }"
      @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <option value="">{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
    </select>
    <p v-if="error" :id="`${id}-error`" class="text-sm text-rose-600" role="alert">{{ error }}</p>
  </div>
</template>
