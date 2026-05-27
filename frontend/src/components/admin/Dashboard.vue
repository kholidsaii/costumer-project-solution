<script setup lang="ts">
import { ref } from 'vue';

const adminName = ref(localStorage.getItem('user_name') || 'Admin');

// Data dummy monitoring statistik admin
const stats = ref([
  { name: 'Total Prospek Leads', value: '12 Data', icon: '📈', color: 'border-blue-500 text-blue-600 bg-blue-50/50' },
  { name: 'Produk Terdaftar', value: '4 Sistem', icon: '🛒', color: 'border-purple-500 text-purple-600 bg-purple-50/50' },
  { name: 'Tiket Support Masuk', value: '3 Tiket', icon: '🎧', color: 'border-red-500 text-red-600 bg-red-50/50' },
  { name: 'Artikel Media', value: '8 Post', icon: '📰', color: 'border-emerald-500 text-emerald-600 bg-emerald-50/50' }
]);
</script>

<template>
  <div class="space-y-6">
    
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-6 md:p-8 rounded-2xl border border-slate-700 shadow-md text-white">
      <h1 class="text-2xl md:text-3xl font-black">Selamat Datang, {{ adminName }}! 👑</h1>
      <p class="text-slate-400 text-sm mt-1 font-medium">
        Ini adalah pusat kendali ekosistem Kerjapro Solutions. Melalui halaman ini Anda bisa mengelola leads dari marketing, menerbitkan produk baru, dan merilis artikel informasi.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="stat in stats" 
        :key="stat.name"
        :class="['bg-white p-6 rounded-2xl border border-l-[6px] shadow-sm flex justify-between items-start transition hover:shadow-md', stat.color]"
      >
        <div class="space-y-1">
          <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ stat.name }}</p>
          <p class="text-2xl font-black text-slate-800">{{ stat.value }}</p>
        </div>
        <span class="text-2xl bg-white p-2 rounded-xl shadow-inner">{{ stat.icon }}</span>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-1">
        <h3 class="text-base font-black text-slate-800 mb-4 flex items-center gap-2">
          ⚙️ Akses Cepat Modul
        </h3>
        <div class="space-y-2">
          <router-link to="/admin/sales" class="block w-full text-center py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            Kelola Leads Prospek Marketing
          </router-link>
          <router-link to="/admin/products" class="block w-full text-center py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            Tambah Katalog Produk Baru
          </router-link>
          <router-link to="/admin/media" class="block w-full text-center py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            Tulis Artikel / Berita Baru
          </router-link>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
        <h3 class="text-base font-black text-slate-800 mb-4 flex items-center gap-2">
          🔔 Aktivitas Sistem Terbaru
        </h3>
        <div class="space-y-3">
          <div class="flex gap-3 text-xs border-b border-slate-100 pb-2.5">
            <span class="text-emerald-500 font-bold">[LEADS]</span>
            <p class="text-slate-600 font-medium">Kholid Saifullah menambahkan data Prospek baru dari perusahaan Netkrom Solution.</p>
          </div>
          <div class="flex gap-3 text-xs border-b border-slate-100 pb-2.5">
            <span class="text-blue-500 font-bold">[SYSTEM]</span>
            <p class="text-slate-600 font-medium">Database PostgreSQL berhasil melakukan sinkronisasi tabel migrasi baru.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>