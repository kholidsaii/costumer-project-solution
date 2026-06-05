<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const userName = ref(localStorage.getItem('user_name') || 'Pelanggan');

// Fungsi untuk menarik nama asli dari database berdasarkan token Google
onMounted(async () => {
  try {
    const response = await api.get('/user');
    userName.value = response.data.name; // Timpa nama dengan nama asli dari Gmail
    localStorage.setItem('user_name', response.data.name); // Simpan agar stabil
  } catch (error) {
    console.error('Gagal mengambil profil user', error);
  }
});

const menuItems = [
  { name: 'Overview', icon: '📊', route: '/dashboard/customer' },
  { name: 'Product Portal', icon: '🛍️', route: '/dashboard/customer/products' },
  { name: 'Pesanan Saya', icon: '📦', route: '/dashboard/customer/orders' },
  { name: 'Tagihan & Billing', icon: '💳', route: '/dashboard/customer/billing' },
  { name: 'Support', icon: '🎧', route: '/dashboard/customer/support' },
];

const handleLogout = async () => {
  try { await api.post('/logout'); } catch (error) {} 
  finally {
    localStorage.clear();
    router.push('/customer');
  }
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans flex">
    
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-850 hidden md:flex">
      <div class="p-6 border-b border-slate-800 flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center font-black text-white text-lg">K</div>
        <div>
          <h2 class="text-white font-black tracking-wider text-sm">KERJAPRO</h2>
          <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Portal Solusi</p>
        </div>
      </div>
      <nav class="flex-1 p-4 space-y-1.5">
        <router-link v-for="item in menuItems" :key="item.name" :to="item.route" exact-active-class="bg-slate-800 text-white font-bold" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm hover:bg-slate-800 hover:text-white transition group">
          <span class="text-lg opacity-70 group-hover:opacity-100">{{ item.icon }}</span>
          {{ item.name }}
        </router-link>
      </nav>
      <div class="p-4 border-t border-slate-800 space-y-1">
        <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs text-red-400 hover:bg-red-950/30 hover:text-red-300 transition text-left">
          <span>🚪</span> Keluar / Logout
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      
      <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
          <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider hidden md:block">Customer Area</h2>
        </div>
        
        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="text-sm font-black text-slate-800">{{ userName }}</p>
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Client Account</p>
          </div>
          <div class="w-10 h-10 bg-[#B48440]/10 text-[#B48440] font-black rounded-xl flex items-center justify-center border border-[#B48440]/20 shadow-sm">
            {{ userName.charAt(0).toUpperCase() }}
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-6 md:p-8">
        <router-view />
      </main>
    </div>
  </div>
</template>