/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js", 
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./resources/views/filament/**/*.blade.php",
    "./public/css/filament/**/*.css",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        },
      },
    },
  },
  plugins: [],
}
