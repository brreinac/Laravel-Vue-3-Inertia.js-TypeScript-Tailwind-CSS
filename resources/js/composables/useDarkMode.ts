import { ref } from 'vue';

const storageKey = 'qvox.dark-mode';
const initial = typeof window !== 'undefined' && window.localStorage.getItem(storageKey) === 'true';
const isDark = ref(initial);

function apply(value: boolean): void {
  document.documentElement.classList.toggle('dark', value);
  window.localStorage.setItem(storageKey, String(value));
}

if (typeof document !== 'undefined') apply(initial);

export function useDarkMode() {
  const toggle = (): void => {
    isDark.value = !isDark.value;
    apply(isDark.value);
  };

  return { isDark, toggle };
}
