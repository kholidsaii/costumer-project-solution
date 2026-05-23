<script setup lang="ts">
// Anda bisa menambahkan logika fetch data ringkasan di sini nanti
const stats = [
  { label: 'Total Projects', value: '12', color: 'text-indigo-600' },
  { label: 'Active Leads', value: '8', color: 'text-emerald-600' },
  { label: 'Pending Billing', value: 'Rp 25M', color: 'text-amber-600' }
];
</script>

<template>
  <div class="p-8">
    <div class="mb-8">
      <h1 class="text-2xl font-black text-slate-800 uppercase italic">Welcome Back!</h1>
      <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Overview Project Solutions</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.label" 
           class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">{{ stat.label }}</p>
        <h3 :class="['text-3xl font-black mt-2', stat.color]">{{ stat.value }}</h3>
      </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
      <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Recent Activity</h3>
      <div class="text-[11px] text-slate-500 font-bold">
        <p class="py-2 border-b border-slate-50">Sistem EMR berhasil dideploy ke RS Polri.</p>
        <p class="py-2 border-b border-slate-50">Update modul marketing untuk tim sales.</p>
        <p class="py-2">Pembayaran invoice dari PT. Maju Jaya diterima.</p>
      </div>
    </div>
  </div>
</template>