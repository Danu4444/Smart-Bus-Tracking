import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  base: './',   // relative paths — works both on XAMPP and Railway
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    port: 5173
  },
  build: {
    outDir: '../app',
    emptyOutDir: true
  }
})
