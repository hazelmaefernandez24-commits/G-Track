/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
        colors: {
            'primary': '#22bbea',
            'secondary': '#ff9933',
            'tertiary': '#212121',
            'quaternary': '#F4EBD3',
        },
        fontSize: {
            'tiny': '.625rem',
        },
        fontFamily: {
            'sans': ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        },
    },
  },
  plugins: [],
}
