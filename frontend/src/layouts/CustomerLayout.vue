<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

// Mengecek path URL secara langsung agar lebih akurat
const isAuthPage = computed(() => {
  return route.path.includes('/login') || route.path.includes('/register');
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans overflow-x-hidden flex flex-col relative pb-32">
    
    <div class="px-4 md:px-8 pt-4 md:pt-8 flex-none">
      <header class="relative w-full max-w-5xl mx-auto h-[160px] md:h-[180px] bg-cover bg-center rounded-2xl shadow-sm border border-slate-200 overflow-hidden" style="background-image: url('/Header.png');">
        
        <div class="absolute left-6 md:left-12 bottom-[15px] w-[90px] h-[90px] md:w-[110px] md:h-[110px] bg-white border-[3px] border-blue-500 rounded-2xl shadow-md flex items-center justify-center z-20 overflow-hidden p-1">
          <img src="/logo.png" alt="Logo" class="w-full h-full object-contain" onerror="this.outerHTML='<div class=\'text-5xl md:text-6xl font-black text-blue-500\'>K</div>'" />
        </div>

        <div class="absolute left-[110px] md:left-[180px] bottom-[70px] z-10">
          <h1 class="text-2xl md:text-4xl font-black text-white tracking-tight drop-shadow-2xl [text-shadow:3px_4px_6px_rgba(0,0,0,0.8)] [-webkit-text-stroke:1px_#3AA0D1]">
            KERJAPRO <span class="text-white [-webkit-text-stroke:1px_#3AA0D1]">SOLUTIONS</span>
          </h1>
          <p class="text-[8px] md:text-xs text-white uppercase tracking-widest font-bold drop-shadow-lg [text-shadow:1px_2px_4px_rgba(0,0,0,0.8)] mt-1">
            Aplikasi Project Management Solutions
          </p>
        </div>

        <div class="absolute bottom-0 left-0 w-full h-[55px] md:h-[65px] bg-white z-0"></div>

        <nav class="absolute left-[110px] md:left-[180px] bottom-0 h-[55px] md:h-[65px] flex items-center gap-3 lg:gap-6 z-10">
          <template v-if="!isAuthPage">
            <router-link to="/customer" class="text-[13px] md:text-[15px] font-black text-[#60A5FA] hover:text-blue-800 transition">Home</router-link>
            <span class="text-slate-300 font-light hidden md:inline">|</span>
            <router-link to="/customer/products" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">Products</router-link>
            <span class="text-slate-300 font-light hidden md:inline">|</span>
            <router-link to="/customer/media" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">Media</router-link>
            <span class="text-slate-300 font-light hidden md:inline">|</span>
            <router-link to="/customer/support" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">Support</router-link>
            <span class="text-slate-300 font-light hidden md:inline">|</span>
            <router-link to="/customer/about" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">About</router-link>
          </template>

          <template v-else>
            <router-link to="/customer/support" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">Support</router-link>
            <span class="text-slate-300 font-light hidden md:inline">|</span>
            <router-link to="/customer/about" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">About</router-link>
          </template>
        </nav>

        <div class="absolute right-4 md:right-8 bottom-0 h-[55px] md:h-[65px] flex items-center gap-2 md:gap-3 z-10">
          <template v-if="!isAuthPage">
            <router-link 
              to="/customer/register" 
              class="flex items-center justify-center min-w-[90px] md:min-w-[110px] py-2 md:py-2.5 text-xs md:text-sm font-black text-[#4BB4E6] bg-white rounded-full border-2 border-[#4BB4E6] shadow-[0_4px_10px_rgba(0,0,0,0.04)] hover:bg-[#4BB4E6] hover:text-white hover:shadow-[0_6px_15px_rgba(75,180,230,0.3)] transition-all"
            >
              Sign up
            </router-link>
            <router-link 
              to="/customer/login" 
              class="flex items-center justify-center min-w-[90px] md:min-w-[110px] py-2 md:py-2.5 text-xs md:text-sm font-black text-white bg-[#4BB4E6] rounded-full border-2 border-[#4BB4E6] shadow-[0_4px_10px_rgba(75,180,230,0.3)] hover:bg-[#3AA0D1] hover:border-[#3AA0D1] transition-all"
            >
              Sign in
            </router-link>
          </template> 

          <template v-else>
            <router-link to="/customer" class="px-5 py-1.5 md:px-6 md:py-2 text-xs md:text-sm font-bold text-white bg-[#4BB4E6] rounded-full hover:bg-[#3AA0D1] shadow-md transition flex items-center gap-2">
              <span>&larr;</span> Home
            </router-link>
          </template>
        </div>
      </header>
    </div>

    <main class="flex-grow w-full z-10 relative">
      <router-view />
    </main>

    <div class="fixed bottom-3 left-3 md:bottom-8 md:left-5 z-50 flex flex-col items-start gap-3 text-sm font-bold text-slate-500 pointer-events-auto">
      <router-link to="/customer/about" class="hover:text-blue-500 transition-colors drop-shadow-md">Tentang Kami</router-link>
      <a href="#" class="hover:text-blue-500 transition-colors drop-shadow-md">Privacy & Policy</a>
      <a href="#" class="hover:text-blue-500 transition-colors drop-shadow-md">Syarat & Ketentuan</a>
    </div>

    <a 
      href="https://wa.me/6285117001162" 
      target="_blank" 
      class="fixed bottom-6 right-6 md:bottom-10 md:right-8 z-50 flex items-center justify-center w-14 h-14 bg-[#25D366] text-white rounded-full shadow-[0_6px_16px_rgba(37,211,102,0.4)] hover:bg-[#1DA851] hover:scale-110 transition-transform duration-300 pointer-events-auto group"
      title="Hubungi Kami di WhatsApp"
    >
      <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M12.031 0C5.385 0 .003 5.384.003 12.031c0 2.12.553 4.19 1.604 6.012L.003 24l6.115-1.604a11.966 11.966 0 005.913 1.572h.004c6.645 0 12.03-5.384 12.03-12.031S18.679 0 12.031 0zm0 21.965h-.003a9.97 9.97 0 01-5.076-1.36l-.364-.216-3.774.99.1-.097.989-3.772-.237-.376a9.96 9.96 0 01-1.528-5.267c0-5.498 4.473-9.972 9.974-9.972 2.664 0 5.166 1.037 7.049 2.92a9.927 9.927 0 012.923 7.052c-.001 5.498-4.475 9.972-9.974 9.972zm5.474-7.48c-.3-.15-1.774-.876-2.049-.976-.275-.101-.476-.15-.675.15-.2.3-.775.976-.95 1.176-.176.2-.351.225-.651.075-.3-.15-1.265-.466-2.408-1.472-.89-.783-1.491-1.751-1.666-2.051-.175-.3-.019-.462.131-.612.134-.135.3-.35.45-.525.15-.175.2-.3.3-.5.1-.2.05-.375-.025-.525-.075-.15-.676-1.626-.926-2.226-.244-.585-.492-.505-.675-.515-.175-.01-.376-.01-.576-.01-.2 0-.526.075-.801.4s-1.051 1.026-1.051 2.502 1.076 2.88 1.226 3.08c.15.2 2.099 3.204 5.086 4.492.711.307 1.266.49 1.696.627.713.227 1.362.195 1.874.118.575-.086 1.774-.726 2.024-1.427.25-.7.25-1.301.175-1.426-.075-.126-.275-.201-.575-.351z"/>
      </svg>
    </a>

  </div>
</template>