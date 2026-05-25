<script setup lang="ts">
import { ref } from 'vue';

const isContentExpanded = ref(false);
const toggleContent = () => { isContentExpanded.value = !isContentExpanded.value; };

// Kita hapus properti 'icon' yang berisi teks karakter, 
// karena kita akan menggunakan icon SVG langsung di template agar hasilnya profesional.
const features = [
  { name: 'WORK', path: '/customer/orders' },
  { name: 'FINANCE', path: '/customer/billing' },
  { name: 'PRODUCT', path: '/customer/products' },
  { name: 'REPORT', path: '/customer' },
  { name: 'SUPPORT', path: '/customer/support' },
];
</script>

<template>
  <div class="px-4 md:px-8 mt-4 md:mt-6 pb-12">
    <main class="relative w-full max-w-7xl mx-auto rounded-2xl shadow-sm border border-slate-200 overflow-hidden pt-12 md:pt-16 pb-12 px-4 md:px-8 text-center z-10">
      
      <div class="absolute inset-0 z-0 pointer-events-none bg-cover bg-center" style="background-image: url('/bg.png');"></div>
      <div class="absolute inset-0 bg-white/15 z-0 pointer-events-none"></div>

      <div class="relative z-10">
        
        <h2 class="kotta-title mb-4 drop-shadow-sm bg-gradient-to-r from-[#4BB4E6] to-[#F2A961] bg-clip-text text-transparent inline-block">
          All-in-one platform <br />
          for project management
        </h2>
        
        <p class="font-playfair italic text-slate-600 text-base md:text-lg mb-6 tracking-wide">
          Manage projects, documents, and team communication in one integrated workspace.
        </p>

        <div class="mb-10">
          <div class="inline-block bg-[#4BB4E6] text-white font-black text-[10px] md:text-xs tracking-widest uppercase px-6 md:px-10 py-3 rounded-full shadow-[0_4px_12px_rgba(75,180,230,0.4)] border border-[#3AA0D1]">
            Communication, Coordination, Discussion, and Execution
          </div>
        </div>

        <p class="font-inter text-xs md:text-sm text-slate-400 italic mb-6">What features from our website would you like to use?</p>
        
        <div class="flex flex-wrap justify-center gap-3 md:gap-4 mb-12">
          <router-link 
            v-for="feat in features" 
            :key="feat.name" 
            :to="feat.path" 
            class="w-[85px] h-[85px] bg-[#0F172A] text-white rounded-[1.25rem] flex flex-col items-center justify-center gap-2 shadow-xl hover:bg-slate-800 hover:-translate-y-1 cursor-pointer transition-all duration-300 border border-slate-700/50"
          >
            <svg v-if="feat.name === 'WORK'" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5L21 3v6l-4.5 4.5M3 21l7.5-7.5m0 0l-3-3m3 3l3 3M7.5 10.5L3 3v6l4.5 4.5M21 21l-7.5-7.5m0 0l3-3m-3 3l-3 3" />
            </svg>

            <svg v-if="feat.name === 'FINANCE'" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H4.5A2.25 2.25 0 002.25 12v6.75A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V12z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5V7.5A2.25 2.25 0 016.75 5.25h10.5A2.25 2.25 0 0119.5 7.5v3M12 15h.008v.008H12V15z" />
            </svg>

            <svg v-if="feat.name === 'PRODUCT'" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>

            <svg v-if="feat.name === 'REPORT'" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>

            <svg v-if="feat.name === 'SUPPORT'" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>

            <span class="text-[10px] font-black tracking-widest mt-1">{{ feat.name }}</span>
          </router-link>
        </div>

        <div class="flex items-center justify-center my-8 relative">
          <div class="h-px bg-slate-300 flex-1 max-w-xs"></div>
          <button @click="toggleContent" class="mx-4 w-10 h-10 bg-gray-800 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-gray-700 transition-transform duration-300" :class="{ 'rotate-180': isContentExpanded }">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div class="h-px bg-slate-300 flex-1 max-w-xs"></div>
        </div>

        <transition name="slide-fade">
          <div v-show="isContentExpanded" class="mt-12 space-y-16 text-left pb-10">
            <div class="flex flex-col md:flex-row items-center gap-0 relative">
              <div class="w-full md:w-1/2 z-10">
                <img src="/g1.png" alt="Assessment" class="w-full h-auto object-cover shadow-2xl rounded-lg" />
              </div>
              <div class="w-full md:w-1/2 bg-white/95 p-8 md:p-12 shadow-xl border border-slate-100 rounded-lg md:-ml-8 z-20 mt-4 md:mt-0 backdrop-blur-sm">
                <h4 class="text-2xl md:text-3xl font-serif text-slate-800 mb-4">Assessment</h4>
                <p class="text-slate-500 text-sm leading-relaxed italic">
                  Every successful project begins with a strong foundation. Kerjapro helps you evaluate your team's readiness, identify strengths dan potential bottlenecks.
                </p>
              </div>
            </div>

            <div class="flex flex-col md:flex-row-reverse items-center gap-0 relative">
              <div class="w-full md:w-1/2 z-10">
                <img src="/g2.png" alt="Implementation" class="w-full h-auto object-cover shadow-2xl rounded-lg" />
              </div>
              <div class="w-full md:w-1/2 bg-white/95 p-8 md:p-12 shadow-xl border border-slate-100 rounded-lg md:-mr-8 z-20 mt-4 md:mt-0 backdrop-blur-sm">
                <h4 class="text-2xl md:text-3xl font-serif text-slate-800 mb-4">Implementation</h4>
                <p class="text-slate-500 text-sm leading-relaxed italic">
                  From the initial planning to the finish line, Kerjapro is here to ensure your project runs smoothly.
                </p>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kotta+One&display=swap');
.kotta-title { font-family: 'Kotta One', serif; font-size: 50px; font-weight: 400; line-height: 1.2; }
@media (max-width: 768px) { .kotta-title { font-size: 32px; } }
.slide-fade-enter-active { transition: all 0.5s ease-out; }
.slide-fade-leave-active { transition: all 0.4s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(-20px); opacity: 0; }
</style>