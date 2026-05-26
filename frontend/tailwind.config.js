/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        navy: {
          50:  '#EEF2F8', 100: '#D6DEEC', 200: '#AEBDD9', 300: '#7E94BD',
          400: '#4F6CA0', 500: '#2C4B82', 600: '#1A3A6D', 700: '#0F2E5C',
          800: '#0A234A', 900: '#051736',
        },
        gold: {
          50:  '#FEF8E6', 100: '#FCEDBF', 200: '#F9DD8C', 300: '#F6CC5C',
          400: '#F4B942', 500: '#E0A22B', 600: '#B7831C', 700: '#8C6314',
          800: '#5F420B', 900: '#3A2806',
        },
        paper: {
          50:  '#FBF9F3', 100: '#F5F1E8', 200: '#ECE6D5', 300: '#D9D0B8',
          400: '#B8AC91', 500: '#8F8467', 600: '#6B6248', 700: '#4A4332',
          800: '#2D2820', 900: '#181410',
        },
      },
      fontFamily: {
        display: ['Outfit', 'ui-rounded', 'system-ui', 'sans-serif'],
        body:    ['Nunito', 'ui-rounded', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        'xs': '4px', 'sm': '8px', 'md': '12px', 'lg': '16px',
        'xl': '24px', '2xl': '32px', 'pill': '999px',
      },
      boxShadow: {
        'xs': '0 1px 2px rgba(15,46,92,0.06)',
        'sm': '0 2px 4px rgba(15,46,92,0.06), 0 1px 2px rgba(15,46,92,0.04)',
        'md': '0 6px 16px rgba(15,46,92,0.08), 0 2px 4px rgba(15,46,92,0.04)',
        'lg': '0 14px 32px rgba(15,46,92,0.10), 0 4px 8px rgba(15,46,92,0.05)',
        'xl': '0 24px 56px rgba(15,46,92,0.14), 0 8px 16px rgba(15,46,92,0.06)',
        'focus': '0 0 0 4px rgba(244,185,66,0.35)',
      },
    },
  },
  plugins: [],
}
