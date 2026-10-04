import forms from '@tailwindcss/forms';

export default {
  content: ['./resources/views/**/*.blade.php', './resources/js/**/*.{vue,ts}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#eff6ff',
          100: '#dbeafe',
          500: '#2563eb',
          600: '#1d4ed8',
          700: '#1d4ed8',
        },
      },
    },
  },
  plugins: [forms],
};
