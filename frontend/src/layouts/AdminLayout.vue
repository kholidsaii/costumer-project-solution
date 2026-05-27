<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const adminName = ref(localStorage.getItem('user_name') || 'Admin');

// Menu navigasi administrator sesuai skema Excel Anda
const menuGroups = [
  {
    name: 'Utama',
    items: [
      { name: 'Dashboard', icon: '📊', route: '/admin/dashboard' }
    ]
  },
  {
    name: 'Manajemen Bisnis',
    items: [
      { name: 'Sales & Leads', icon: '📈', route: '/admin/sales' },
      { name: 'Kelola Produk', icon: '🛒', route: '/admin/products' },
    ]
  },
  {
    name: 'Konten Portal',
    items: [
      { name: 'Media & Artikel', icon: '📰', route: '/admin/media' },
      { name: 'Support & Tiket', icon: '🎧', route: '/admin/support' },
    ]
  },
  {
    name: 'Sistem',
    items: [
      { name: 'Pengaturan / Setup', icon: '⚙️', route: '/admin/setup' }
    ]
  }
];

const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    localStorage.clear();
    router.push('/customer/login');
  }
};
</script>

<template>
  <div class="min-h-screen bg-slate-100 font-sans flex">
    
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 hidden md:flex">
      <div class="p-6 border-b border-slate-800 flex items-center gap-3">
        <div class="w-9 h-9 bg-[#B48440] rounded-xl flex items-center justify-center font-black text-white text-xl shadow-md">
          A
        </div>
        <div>
          <h2 class="text-white font-black tracking-wider text-sm">KERJAPRO</h2>
          <p class="text-[10px] text-[#B48440] font-bold uppercase tracking-widest">Admin Control</p>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto p-4 space-y-6">
        <div v-for="group in menuGroups" :key="group.name" class="space-y-2">
          <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-4">
            {{ group.name }}
          </p>
          
          <router-link 
            v-for="item in group.items" 
            :key="item.name" 
            :to="item.route"
            exact-active-class="bg-[#B48440] text-white font-bold shadow-md"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-slate-800 hover:text-white transition group"
          >
            <span class="text-lg opacity-80 group-hover:opacity-100">{{ item.icon }}</span>
            {{ item.name }}
          </router-link>
        </div>
      </div>

      <div class="p-4 border-t border-slate-800 space-y-1">
        <router-link to="/customer" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs hover:text-white transition">
          <span>🏠</span> Lihat Halaman Depan
        </router-link>
        <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs text-red-400 hover:bg-red-950/30 hover:text-red-300 transition text-left font-bold">
          <span>🚪</span> Keluar / Logout
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      
      <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shadow-sm z-10">
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Control Panel</h2>
        
        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="text-sm font-black text-slate-800">{{ adminName }}</p>
            <p class="text-[11px] text-red-500 font-bold uppercase tracking-wider">Super Administrator</p>
          </div>
          <div class="w-10 h-10 bg-slate-900 text-white font-black rounded-xl flex items-center justify-center border border-slate-800">
            👑
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-6 md:p-8">
        <router-view />
      </main>
    </div>

  </div>
</template>