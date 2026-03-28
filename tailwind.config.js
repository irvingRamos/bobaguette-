/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'bob-verde': '#1B4332',
        'bob-crema': '#FDFBF7',
        'bob-fondo': '#E7DCC8',
      },
      // Agrega esto para que Tailwind reconozca la fuente de Google
      fontFamily: {
        'instrument': ['"Instrument Sans"', 'sans-serif'],
      },
    },
  },
  plugins: [],
}