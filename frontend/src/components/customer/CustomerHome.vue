<script setup lang="ts">
import { ref } from 'vue';

const isContentExpanded = ref(false);
const toggleContent = () => { isContentExpanded.value = !isContentExpanded.value; };

const features = [
  { name: 'WORK', icon: '🤝', path: '/customer/orders' },
  { name: 'FINANCE', icon: '💳', path: '/customer/billing' },
  { name: 'PRODUCT', icon: '🛒', path: '/customer/products' },
  { name: 'REPORT', icon: '📄', path: '/customer' },
  { name: 'SUPPORT', icon: '🎧', path: '/customer/support' },
];
</script>

<template>
  <div class="px-4 md:px-8 mt-4 md:mt-6 pb-12">
    <main class="relative w-full max-w-7xl mx-auto rounded-2xl shadow-sm border border-slate-200 overflow-hidden pt-12 md:pt-16 pb-12 px-4 md:px-8 text-center z-10">
      <div class="absolute inset-0 z-0 pointer-events-none bg-cover bg-center" style="background-image: url('/bg.png');"></div>
      <div class="absolute inset-0 bg-white/15 z-0 pointer-events-none"></div>

      <div class="relative z-10">
        <h2 class="kotta-title mb-6 drop-shadow-sm bg-gradient-to-r from-[#4BB4E6] to-[#F2A961] bg-clip-text text-transparent inline-block">
          All-in-one platform <br />
          for project management
        </h2>
        
        <p class="font-playfair italic text-slate-500 text-base md:text-lg mb-8 tracking-wide">
          Manage projects, documents, and team communication in one integrated workspace.
        </p>

        <p class="font-inter text-xs md:text-sm text-slate-400 italic mb-4">What features from our website would you like to use?</p>
        
        <div class="flex flex-wrap justify-center gap-3 md:gap-4 mb-12">
          <router-link v-for="feat in features" :key="feat.name" :to="feat.path" class="w-20 h-20 bg-gray-800 text-white rounded-xl flex flex-col items-center justify-center gap-1 shadow-md hover:bg-gray-700 hover:scale-105 cursor-pointer transition-all duration-300">
            <span class="text-2xl">{{ feat.icon }}</span>
            <span class="text-[10px] font-bold tracking-wider">{{ feat.name }}</span>
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