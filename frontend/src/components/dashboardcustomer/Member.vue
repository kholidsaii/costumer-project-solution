<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../api/axios';

const user = ref<any>(null);
const softwareOrders = ref<any[]>([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const resUser = await api.get('/customer/me');
    user.value = resUser.data;

    // Ambil order software yang sudah Lunas untuk ditampilkan link-nya
    const resOrders = await api.get('/customer/orders');
    // Filter pesanan milik user ini yang berjenis 'software' dan status 'paid'
    softwareOrders.value = resOrders.data.filter((o: any) => 
      o.product?.product_type === 'software' && o.status === 'paid'
    );
  } catch (error) { 
    console.error(error); 
  } finally { 
    loading.value = false; 
  }
});
</script>

<template>
  <div class="mb-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 mb-6">
      <h2 class="text-xl font-black text-slate-800 mb-6">Status Member Anda</h2>
      
      <div v-if="loading" class="text-slate-500 font-bold animate-pulse">Memuat info database...</div>
      
      <div v-else-if="user" class="space-y-6">
        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between">
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase">Tier Saat Ini</p>
            <p class="text-2xl font-black text-blue-700 capitalize">{{ user.tier_name }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="border border-slate-200 p-4 rounded-xl">
            <p class="font-black text-slate-800">Limit Download Digital</p>
            <div class="mt-2 text-sm text-slate-600">
              Terpakai: <strong class="text-indigo-600">{{ user.digital_downloads_count }}</strong> / 
              <span class="font-bold">{{ user.tier?.digital_limit >= 999999 ? 'Unlimited' : user.tier?.digital_limit + 'x' }}</span>
            </div>
            <div class="w-full bg-slate-100 h-1.5 mt-3 rounded-full overflow-hidden">
                <div class="bg-indigo-500 h-full" :style="{ width: user.tier?.digital_limit >= 999999 ? '100%' : Math.min((user.digital_downloads_count / user.tier?.digital_limit) * 100, 100) + '%' }"></div>
            </div>
          </div>
          
          <div class="border border-slate-200 p-4 rounded-xl">
            <p class="font-black text-slate-800">Hak Akses Software Khusus</p>
            <div class="mt-2 text-sm">
              <span v-if="user.tier?.software_access" class="bg-green-100 text-green-700 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 w-max">
                ✓ DIIZINKAN AKSES
              </span>
              <span v-else class="bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 w-max">
                ✕ DILARANG (Silakan Upgrade)
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
      <h2 class="text-lg font-black text-slate-800 mb-2">Akses Lisensi Software Anda</h2>
      <p class="text-xs font-bold text-slate-500 mb-6">Software yang Anda pesan dan bukti transfernya telah disetujui Admin akan muncul tautan rahasianya di bawah ini.</p>

      <div v-if="loading" class="text-slate-500 text-sm font-bold">Memeriksa lisensi...</div>
      <div v-else-if="softwareOrders.length === 0" class="text-center p-8 border border-dashed border-slate-300 rounded-xl text-slate-400 font-bold text-sm">
        Belum ada lisensi software yang aktif.
      </div>
      
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="order in softwareOrders" :key="order.id" class="border border-indigo-100 bg-indigo-50/30 p-4 rounded-xl shadow-sm hover:shadow-md transition flex flex-col">
          <h3 class="font-black text-indigo-900 text-sm line-clamp-1">{{ order.product?.name }}</h3>
          <p class="text-[10px] font-bold text-slate-400 mt-1 mb-4">INV-{{ order.order_number }}</p>
          
          <div class="mt-auto pt-4 border-t border-indigo-100/50">
            <a v-if="order.software_link" :href="order.software_link" target="_blank" class="block text-center bg-indigo-600 text-white w-full py-2.5 rounded-lg text-xs font-black hover:bg-indigo-700 transition shadow-sm">
              🚀 Buka Tautan Lisensi
            </a>
            <button v-else disabled class="block text-center bg-slate-100 text-slate-400 w-full py-2.5 rounded-lg text-[10px] font-bold cursor-not-allowed">
              Menunggu Admin Memasukkan Link
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>