<script setup lang="ts">
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans overflow-x-hidden">
    
    <div class="px-4 md:px-8 pt-4 md:pt-6">
      <header class="relative w-full max-w-7xl mx-auto h-[160px] md:h-[180px] bg-cover bg-center rounded-2xl shadow-sm border border-slate-200 overflow-hidden" style="background-image: url('/Header.png');">
        
        <div class="absolute left-6 md:left-12 bottom-[15px] w-[90px] h-[90px] md:w-[110px] md:h-[110px] bg-white border-[3px] border-blue-500 rounded-2xl shadow-md flex items-center justify-center z-20 overflow-hidden">
          <div class="text-5xl md:text-6xl font-black text-blue-500">K</div>
        </div>

        <div class="absolute left-[110px] md:left-[180px] bottom-[70px] z-10">
          <h1 class="text-2xl md:text-4xl font-black text-white tracking-tight drop-shadow-2xl [text-shadow:3px_4px_6px_rgba(0,0,0,0.8)] [-webkit-text-stroke:1px_#B48440]">
            KERJAPRO <span class="text-white [-webkit-text-stroke:1px_#B48440]">SOLUTIONS</span>
          </h1>
          <p class="text-[8px] md:text-xs text-white uppercase tracking-widest font-bold drop-shadow-lg [text-shadow:1px_2px_4px_rgba(0,0,0,0.8)] mt-1">
            Aplikasi Project Management Solutions
          </p>
        </div>

        <div class="absolute bottom-0 left-0 w-full h-[55px] md:h-[65px] bg-white z-0"></div>

        <nav class="absolute left-[110px] md:left-[180px] bottom-0 h-[55px] md:h-[65px] flex items-center gap-3 lg:gap-6 z-10">
          <router-link to="/customer" class="text-[13px] md:text-[15px] font-black text-[#1E3A8A] hover:text-blue-800 transition">Home</router-link>
          <span class="text-slate-300 font-light hidden md:inline">|</span>
          <router-link to="/customer/products" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">Products</router-link>
          <span class="text-slate-300 font-light hidden md:inline">|</span>
          <router-link to="/customer/media" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">Media</router-link>
          <span class="text-slate-300 font-light hidden md:inline">|</span>
          <router-link to="/customer/support" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">Support</router-link>
          <span class="text-slate-300 font-light hidden md:inline">|</span>
          <router-link to="/customer/about" class="text-[13px] md:text-[15px] font-bold text-[#60A5FA] hover:text-blue-600 transition">About</router-link>
        </nav>

        <div class="absolute right-4 md:right-8 bottom-0 h-[55px] md:h-[65px] flex items-center gap-2 md:gap-3 z-10">
          <router-link to="/customer/register" class="px-4 py-1.5 md:px-6 md:py-1.5 text-xs md:text-sm font-bold text-[#B48440] bg-white border-2 border-[#B48440] rounded-xl hover:bg-orange-50 transition">
            Register
          </router-link>
          <router-link to="/customer/login" class="px-4 py-1.5 md:px-6 md:py-2 text-xs md:text-sm font-bold text-white bg-[#B48440] rounded-xl hover:bg-[#966d33] shadow-md transition">
            Login
          </router-link>
        </div>
      </header>
    </div>

    <router-view />

  </div>
</template>