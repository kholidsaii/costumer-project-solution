<script setup lang="ts">
import { ref } from 'vue';

// Data dummy produk (Nanti ini diambil dari API backend Laravel)
const products = ref([
  {
    id: 1,
    name: 'Sistem EMR Rumah Sakit',
    description: 'Sistem Rekam Medis Elektronik terintegrasi dengan modul lengkap untuk faskes.',
    price: 'Rp 50.000.000',
    category: 'Software',
    image: 'bg-indigo-100 text-indigo-500' // Placeholder warna ilustrasi
  },
  {
    id: 2,
    name: 'Website Company Profile',
    description: 'Website profesional responsif untuk meningkatkan kredibilitas perusahaan Anda.',
    price: 'Rp 5.000.000',
    category: 'Website',
    image: 'bg-emerald-100 text-emerald-500'
  },
  {
    id: 3,
    name: 'Aplikasi HRIS',
    description: 'Manajemen absensi, payroll, dan data karyawan dalam satu platform.',
    price: 'Rp 25.000.000',
    category: 'Software',
    image: 'bg-amber-100 text-amber-500'
  },
  {
    id: 4,
    name: 'Maintenance Server (Bulanan)',
    description: 'Layanan monitoring dan pemeliharaan server untuk menjamin uptime 99.9%.',
    price: 'Rp 2.000.000',
    category: 'Service',
    image: 'bg-slate-200 text-slate-500'
  }
]);

const handleBuy = (productName: string) => {
  alert(`Anda memilih untuk membeli: ${productName}. Fitur checkout akan segera dibuat!`);
};
</script>

<template>
  <div class="px-8">
    <div class="mb-8 flex justify-between items-end">
      <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase italic">Katalog Solusi</h1>
        <p class="text-sm text-slate-500 font-bold mt-2">Pilih sistem atau layanan yang sesuai dengan kebutuhan bisnis Anda.</p>
      </div>
      
      <select class="bg-white border border-slate-200 text-sm font-bold text-slate-600 rounded-lg px-4 py-2 outline-none focus:border-indigo-500 transition">
        <option>Semua Kategori</option>
        <option>Software</option>
        <option>Website</option>
        <option>Service</option>
      </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="product in products" :key="product.id" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col transition hover:shadow-md">
        <div :class="['h-40 w-full flex items-center justify-center font-black uppercase tracking-widest', product.image]">
          {{ product.category }}
        </div>
        
        <div class="p-6 flex flex-col flex-grow justify-between">
          <div>
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-black text-slate-800">{{ product.name }}</h3>
            </div>
            <p class="text-sm text-slate-500 leading-relaxed mb-4">{{ product.description }}</p>
          </div>
          
          <div class="mt-auto border-t border-slate-50 pt-4 flex items-center justify-between">
            <span class="text-lg font-black text-indigo-600">{{ product.price }}</span>
            <button 
              @click="handleBuy(product.name)"
              class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-600 transition-colors"
            >
              Pesan Sekarang
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>