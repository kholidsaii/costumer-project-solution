<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../api/axios';

const router = useRouter();
const route = useRoute();
const userName = ref(localStorage.getItem('user_name') || 'Pelanggan');
const userTierSlug = ref(localStorage.getItem('user_tier_slug') || 'free');
const userTierName = ref(localStorage.getItem('user_tier_name') || 'Free Member');

onMounted(async () => {
  try {
    const response = await api.get('/user');
    userName.value = response.data.name;
    
    userTierSlug.value = response.data.tier?.slug || 'free';
    userTierName.value = response.data.tier?.name || 'Free Member';
    
    localStorage.setItem('user_name', response.data.name);
    localStorage.setItem('user_tier_slug', userTierSlug.value);
    localStorage.setItem('user_tier_name', userTierName.value);
  } catch (error) {
    console.error('Gagal mengambil profil user', error);
  }
});

const menuItems = [
  { name: 'Overview', icon: '📊', route: '/dashboard/customer' },
  { name: 'Info Member', icon: '🧷', route: '/dashboard/customer/member' },
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

// --- LOGIKA TEMA WARNA DINAMIS BERDASARKAN TIER ---
const theme = computed(() => {
  const slug = userTierSlug.value.toLowerCase();
  
  if (slug.includes('gold')) {
    return {
      header: 'bg-gradient-to-r from-amber-50 to-white border-amber-200',
      badge: 'bg-gradient-to-r from-amber-200 to-yellow-400 text-amber-900 border-amber-400 shadow-amber-400/50 animate-pulse',
      avatar: 'bg-gradient-to-tr from-amber-400 to-yellow-600 shadow-amber-500/50',
      activeMenu: 'bg-gradient-to-r from-amber-500 to-yellow-600 text-white shadow-amber-900/30'
    };
  } else if (slug.includes('silver')) {
    return {
      header: 'bg-gradient-to-r from-slate-100 to-white border-slate-300',
      badge: 'bg-gradient-to-r from-slate-200 to-slate-300 text-slate-800 border-slate-400 shadow-slate-400/50',
      avatar: 'bg-gradient-to-tr from-slate-400 to-slate-600 shadow-slate-500/50',
      activeMenu: 'bg-gradient-to-r from-slate-500 to-slate-700 text-white shadow-slate-900/30'
    };
  } else {
    // Default Free Member
    return {
      header: 'bg-white border-slate-200',
      badge: 'bg-blue-50 text-blue-700 border-blue-200 shadow-sm',
      avatar: 'bg-gradient-to-tr from-blue-600 to-indigo-600 shadow-blue-500/50',
      activeMenu: 'bg-blue-600 text-white shadow-blue-900/30'
    };
  }
});
</script>

<template>
  <div class="min-h-screen bg-slate-100 font-sans flex flex-col md:flex-row">
    
    <aside class="w-full md:w-64 bg-slate-900 text-slate-200 flex flex-col flex-none shadow-xl transition-all duration-300">
      <div class="p-6 border-b border-slate-800 flex items-center justify-between">
        <h1 class="text-base font-black tracking-wider text-white uppercase">Kerjapro Customer</h1>
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <router-link 
          v-for="item in menuItems" 
          :key="item.name" 
          :to="item.route" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold tracking-wide transition group"
          :class="[route.path === item.route ? theme.activeMenu : 'hover:bg-slate-800/50 text-slate-400 hover:text-slate-200']"
        >
          <span class="text-lg opacity-70 group-hover:opacity-100">{{ item.icon }}</span>
          {{ item.name }}
        </router-link>
      </nav>
      <div class="p-4 border-t border-slate-800 space-y-1">
        <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs text-red-400 hover:bg-red-950/30 hover:text-red-300 transition text-left font-bold">
          <span>🚪</span> Keluar / Logout
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      <header :class="['h-16 border-b px-6 flex items-center justify-between shadow-sm transition-colors duration-500', theme.header]">
        <div class="flex items-center gap-3">
          <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider hidden md:block">Customer Area</h2>
        </div>
        
        <div class="flex items-center gap-3">
          <div class="text-right flex flex-col items-end justify-center">
            <p class="text-sm font-black text-slate-800 leading-tight">{{ userName }}</p>
            
            <span :class="['mt-0.5 inline-flex items-center text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider border transition-all duration-500', theme.badge]">
              {{ userTierSlug.includes('gold') ? '👑' : (userTierSlug.includes('silver') ? '✨' : '') }} {{ userTierName }}
            </span>
          </div>
          
          <div :class="['w-9 h-9 rounded-xl flex items-center justify-center text-white font-black text-sm transition-all duration-500', theme.avatar]">
            {{ userName.charAt(0).toUpperCase() }}
          </div>
        </div>
      </header>

      <main class="p-6 flex-1 overflow-y-auto">
        <router-view />
      </main>
    </div>

  </div>
</template>