/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {},
    container: {
      padding: {
        DEFAULT: "16px",
        sm: "32px",
        lg: "64px",
        xl: "100px",
      },
    },
  },
  plugins: [],
};
