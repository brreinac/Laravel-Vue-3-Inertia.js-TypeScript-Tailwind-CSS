import { onBeforeUnmount } from 'vue';

export function useDebounce() {
  let timer: ReturnType<typeof setTimeout> | undefined;

  onBeforeUnmount(() => {
    if (timer) clearTimeout(timer);
  });

  return (callback: () => void, delay = 300): void => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(callback, delay);
  };
}
