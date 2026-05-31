import { defineConfig } from 'vite'

export default defineConfig({
  root: '.',
  publicDir: 'assets',
  server: {
    port: 8888,
    open: true
  }
})
