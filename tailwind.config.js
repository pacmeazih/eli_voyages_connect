/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // Enable class-based dark mode
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // ELI-Voyages Brand Colors
        brand: {
          primary: '#003040',      // Dark teal / airplane
          secondary: '#00C0C0',    // Turquoise / sea
          accent: '#F0C000',       // Sun yellow
          orange: '#E06000',       // Orange ring / warm accent
          neutral: '#F0F0F0',      // Light grey/white
        },
        eli: {
          teal: {
            DEFAULT: '#003040',
            50: '#E6F2F5',
            100: '#CCE5EB',
            200: '#99CBD7',
            300: '#66B1C3',
            400: '#3397AF',
            500: '#003040',
            600: '#002633',
            700: '#001D26',
            800: '#00131A',
            900: '#000A0D',
          },
          turquoise: {
            DEFAULT: '#00C0C0',
            50: '#E6F9F9',
            100: '#CCF3F3',
            200: '#99E7E7',
            300: '#66DBDB',
            400: '#33CFCF',
            500: '#00C0C0',
            600: '#009999',
            700: '#007373',
            800: '#004D4D',
            900: '#002626',
          },
          yellow: {
            DEFAULT: '#F0C000',
            50: '#FEF9E6',
            100: '#FDF3CC',
            200: '#FBE799',
            300: '#F9DB66',
            400: '#F7CF33',
            500: '#F0C000',
            600: '#C09900',
            700: '#907300',
            800: '#604D00',
            900: '#302600',
          },
          orange: {
            DEFAULT: '#E06000',
            50: '#FCF0E6',
            100: '#F9E0CC',
            200: '#F3C199',
            300: '#EDA266',
            400: '#E78333',
            500: '#E06000',
            600: '#B34D00',
            700: '#863A00',
            800: '#5A2600',
            900: '#2D1300',
          },
        },
      },
    },
  },
  plugins: [],
}

