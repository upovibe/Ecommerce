/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.{php,html,js}", // Scan PHP, HTML, and JS files for Tailwind classes
    "./includes/**/*.{php,html,js}",
    "./pages/**/*.{php,html,js}",
    "./components/**/*.{php,html,js}", // Add other directories if needed
  ],
  theme: {
    extend: {
      colors: {
        // The primary color will be dynamically injected by PHP in the header
        // Or set a default here if you are not using the CDN script approach
        // primary: '#3B82F6', 
      },
      animation: {
        'fade-in-down': 'fadeInDown 0.3s ease-out',
        'fade-out-up': 'fadeOutUp 0.3s ease-out',
      },
      keyframes: {
        fadeInDown: {
          '0%': { opacity: '0', transform: 'translateY(-10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        fadeOutUp: {
          '0%': { opacity: '1', transform: 'translateY(0)' },
          '100%': { opacity: '0', transform: 'translateY(-10px)' },
        }
      }
    },
  },
  plugins: [],
} 