/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        'playfair': ['"Playfair Display"', 'serif'],
        'bebas': ['"Bebas Neue"', 'sans-serif'],
        'inter': ['"Inter"', 'sans-serif'],
        'kotta': ['"Kotta One"', 'serif'],
      }
    },
  },
  plugins: [],
}