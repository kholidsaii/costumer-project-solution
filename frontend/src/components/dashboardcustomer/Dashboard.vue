<script setup lang="ts">
import { ref } from 'vue';

const userName = ref(localStorage.getItem('user_name') || 'Pelanggan');

const cards = ref([
  { title: 'Project Dalam Pengerjaan', value: '1 Sistem', desc: 'Sistem EMR Rumah Sakit', icon: '🚀', color: 'border-blue-500 text-blue-600 bg-blue-50/50' },
  { title: 'Invoice Belum Terbayar', value: 'Rp 50.000.000', desc: 'Jatuh tempo dalam 7 hari', icon: '💳', color: 'border-amber-500 text-amber-600 bg-amber-50/50' },
  { title: 'Tiket Support Aktif', value: '0 Tiket', desc: 'Semua kendala terselesaikan', icon: '🎧', color: 'border-emerald-500 text-emerald-600 bg-emerald-50/50' }
]);
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm">
      <h1 class="text-2xl md:text-3xl font-black text-slate-800">Halo, {{ userName }}! 👋</h1>
      <p class="text-sm text-slate-500 mt-1 font-medium">
        Melalui panel ini Anda dapat memantau progress pengerjaan aplikasi, status pembayaran invoice, serta mengajukan bantuan teknis.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div 
        v-for="card in cards" 
        :key="card.title"
        :class="['bg-white p-6 rounded-2xl border border-l-[6px] shadow-sm flex justify-between items-start', card.color]"
      >
        <div class="space-y-1">
          <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ card.title }}</p>
          <p class="text-xl font-black text-slate-800">{{ card.value }}</p>
          <p class="text-xs text-slate-500 font-medium">{{ card.desc }}</p>
        </div>
        <span class="text-2xl">{{ card.icon }}</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
      <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
        <span>📢</span> Pengumuman Sistem
      </h3>
      <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-600 leading-relaxed">
        Maintenance server bulanan akan dilakukan pada hari Sabtu pukul 23:00 WIB. Uptime aplikasi Anda dijamin tidak akan terganggu selama proses ini berlangsung.
      </div>
    </div>
  </div>
</template>