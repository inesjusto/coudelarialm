/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/**/*.{js,ts,jsx,tsx}",
    "./components/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#1f3b2c",   // verde escuro rústico
        accent: "#2ecc71",    // verde do logo
        dark: "#0b1220",
      },
    },
  },
  plugins: [],
}
