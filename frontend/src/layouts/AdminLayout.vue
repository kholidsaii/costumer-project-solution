<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const adminName = ref(localStorage.getItem('user_name') || 'Admin');

// Struktur Sidebar Baru
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
      { name: 'Billing', icon: '💳', route: '/admin/billings' },
      { name: 'Customer', icon: '👥', route: '/admin/customers' },
      { name: 'Order', icon: '📦', route: '/admin/orders' },
    ]
  },
  {
    name: 'Manajemen Product',
    items: [
      { name: 'Kelola Products', icon: '🛒', route: '/admin/products' },
    ]
  }
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
        <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center font-black text-white text-lg">K</div>
        <div>
          <h2 class="text-white font-black tracking-wider text-sm">KERJAPRO</h2>
          <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Admin Panel</p>
        </div>
      </div>
      <div class="flex-1 overflow-y-auto p-4 space-y-6">
        <div v-for="group in menuGroups" :key="group.name">
          <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 px-4">{{ group.name }}</h3>
          <nav class="space-y-1">
            <router-link v-for="item in group.items" :key="item.name" :to="item.route" exact-active-class="bg-indigo-600 text-white font-bold shadow-md" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-slate-800 hover:text-white transition group">
              <span class="text-lg opacity-70 group-hover:opacity-100">{{ item.icon }}</span>
              {{ item.name }}
            </router-link>
          </nav>
        </div>
      </div>
      <div class="p-4 border-t border-slate-800 space-y-1">
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
            <p class="text-[11px] text-red-500 font-bold uppercase tracking-wider">Administrator</p>
          </div>
          <div class="w-10 h-10 bg-red-100 text-red-600 font-black rounded-xl flex items-center justify-center border border-red-200 shadow-sm">
            {{ adminName.charAt(0).toUpperCase() }}
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-slate-50/50">
        <router-view />
      </main>
    </div>
  </div>
</template>