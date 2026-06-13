<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const adminName = ref(localStorage.getItem('user_name') || 'Admin');

// Struktur Sidebar Baru dengan Menu Setup Member
const menuGroups = [
  {
    name: 'Utama',
    items: [
      { name: 'Home', icon: '🏠', route: '/admin/dashboard' }
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
      { name: 'Software / Layanan', icon: '💻', route: '/admin/products/software' },
      { name: 'Produk Digital', icon: '📄', route: '/admin/products/digital' },
      { name: 'Fisik / Retail', icon: '📦', route: '/admin/products/physical' },
    ]
  },
  {
    name: 'Member Kontrol',
    items: [
      { name: 'Data & Transaksi', icon: '👥', route: '/admin/members' },
      { name: 'Paket Member', icon: '⚙️', route: '/admin/tiers' },      
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
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between flex-none">
      <div class="p-6">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-8 h-8 bg-blue-500 rounded-xl flex items-center justify-center font-black text-white text-lg shadow-lg shadow-blue-500/30">K</div>
          <span class="font-black text-white tracking-wider text-base">KERJAPRO</span>
        </div>
        
        <div class="space-y-6">
          <div v-for="group in menuGroups" :key="group.name">
            <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">{{ group.name }}</p>
            <nav class="space-y-1">
              <router-link v-for="item in group.items" :key="item.name" :to="item.route" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold transition group" active-class="bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                <span class="text-lg opacity-70 group-hover:opacity-100">{{ item.icon }}</span>
                {{ item.name }}
              </router-link>
            </nav>
          </div>
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
        </div>
      </header>
      <main class="flex-1 p-6 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>