// src/main.ts
import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router' // Impor router

const app = createApp(App)
app.use(router) // Pasang router
app.mount('#app')