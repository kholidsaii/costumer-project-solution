<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../../api/axios';

const tiers = ref<any[]>([]);
const form = ref({ id: null, name: '', description: '', price: 0, digital_limit: 0, software_access: false, duration_in_months: 12 });
const isModalOpen = ref(false);

const fetchTiers = async () => {
  const { data } = await api.get('/admin/tiers');
  tiers.value = data;
};
onMounted(fetchTiers);

const openModal = (tier: any = null) => {
  if (tier) form.value = { ...tier, software_access: tier.software_access === 1 || tier.software_access === true };
  else form.value = { id: null, name: '', description: '', price: 0, digital_limit: 20, software_access: false, duration_in_months: 12 };
  isModalOpen.value = true;
};

const saveTier = async () => {
  try {
    if (form.value.id) await api.put(`/admin/tiers/${form.value.id}`, form.value);
    else await api.post('/admin/tiers', form.value);
    isModalOpen.value = false;
    fetchTiers();
    alert('Tier berhasil disimpan!');
  } catch (error) { alert('Gagal menyimpan tier'); }
};
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h2 class="font-black text-slate-800">Setup Paket Member Tier</h2>
        <p class="text-xs text-slate-500 font-bold">Custom harga, limit digital, akses software & durasi.</p>
      </div>
      <button @click="openModal()" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-black hover:bg-blue-700 shadow-md shadow-blue-500/30 transition">+ Tambah Tier</button>
    </div>
    
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 font-black uppercase tracking-wider">
          <tr>
            <th class="p-4">Nama Tier</th>
            <th class="p-4">Harga & Durasi</th>
            <th class="p-4">Limit Digital</th>
            <th class="p-4">Akses Software</th>
            <th class="p-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm text-slate-600">
          <tr v-for="t in tiers" :key="t.id" class="border-b border-slate-100 hover:bg-slate-50 transition">
            <td class="p-4 font-black text-slate-800">{{ t.name }}</td>
            <td class="p-4">
              <p class="font-black text-indigo-600">Rp {{ Number(t.price).toLocaleString('id-ID') }}</p>
              <p class="text-[10px] text-slate-400 mt-0.5 font-bold uppercase">{{ t.duration_in_months > 0 ? t.duration_in_months + ' Bulan' : 'Selamanya (Lifetime)' }}</p>
            </td>
            <td class="p-4 font-bold">{{ t.digital_limit >= 999999 ? 'Unlimited' : t.digital_limit + 'x Download' }}</td>
            <td class="p-4">
              <span :class="t.software_access ? 'text-emerald-700 bg-emerald-100' : 'text-red-600 bg-red-100'" class="px-2.5 py-1 rounded-md font-black text-[10px] uppercase">
                {{ t.software_access ? 'Diberikan' : 'Dilarang' }}
              </span>
            </td>
            <td class="p-4 text-right space-x-2">
              <button @click="openModal(t)" class="text-blue-600 bg-blue-50 px-4 py-2 rounded-lg text-xs font-black border border-blue-200 hover:bg-blue-100 transition">Edit Akses</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl flex flex-col overflow-hidden max-h-[90vh] animate-fade-in">
        
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
          <h2 class="font-black text-xl text-slate-800">{{ form.id ? 'Edit Paket Member' : 'Tambah Paket Baru' }}</h2>
          <button @click="isModalOpen = false" class="text-slate-400 hover:text-red-500 text-3xl font-black leading-none transition outline-none">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 custom-scrollbar bg-white">
          <form id="tierForm" @submit.prevent="saveTier" class="space-y-5 text-sm">
            
            <div>
              <label class="block font-black text-slate-700 mb-1">Nama Paket <span class="text-red-500">*</span></label>
              <input v-model="form.name" required placeholder="Contoh: Platinum Member" class="w-full border border-slate-200 p-3 rounded-xl bg-slate-50 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 transition font-bold" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-black text-slate-700 mb-1">Harga Langganan <span class="text-red-500">*</span></label>
                <input v-model="form.price" type="number" required min="0" class="w-full border border-slate-200 p-3 rounded-xl bg-slate-50 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 transition font-bold text-indigo-700" />
              </div>
              <div>
                <label class="block font-black text-slate-700 mb-1">Durasi (Bulan) <span class="text-red-500">*</span></label>
                <input v-model="form.duration_in_months" type="number" required min="0" class="w-full border border-slate-200 p-3 rounded-xl bg-slate-50 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 transition font-bold" />
                <p class="text-[10px] text-slate-500 mt-1.5 font-bold uppercase tracking-wide">Isi 0 = Lifetime</p>
              </div>
            </div>

            <div>
              <label class="block font-black text-slate-700 mb-1">Limit Download Digital <span class="text-red-500">*</span></label>
              <input v-model="form.digital_limit" type="number" required min="0" class="w-full border border-slate-200 p-3 rounded-xl bg-slate-50 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 transition font-bold" />
              <p class="text-[10px] text-slate-500 mt-1.5 font-bold uppercase tracking-wide">Isi 999999 = Unlimited</p>
            </div>

            <div class="p-4 border border-indigo-100 rounded-xl bg-indigo-50/50">
              <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" v-model="form.software_access" class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 transition mt-0.5 cursor-pointer"> 
                <div>
                  <span class="font-black text-indigo-900 block">Izinkan Akses Produk Software Khusus</span>
                  <p class="text-[11px] text-indigo-600/80 mt-0.5 font-bold leading-snug">Centang opsi ini jika member dengan tier ini diperbolehkan memesan sistem software eksklusif dari katalog.</p>
                </div>
              </label>
            </div>

            <div>
              <label class="block font-black text-slate-700 mb-1">Deskripsi & Keunggulan</label>
              <textarea v-model="form.description" rows="3" placeholder="Tuliskan keuntungan yang didapat member..." class="w-full border border-slate-200 p-3 rounded-xl bg-slate-50 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 transition font-medium"></textarea>
            </div>
            
          </form>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-white flex justify-end gap-3 shrink-0 rounded-b-3xl">
          <button type="button" @click="isModalOpen = false" class="px-6 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-black hover:bg-slate-200 transition">Batal</button>
          <button type="submit" form="tierForm" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl font-black hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 tracking-wide">
            Simpan Paket
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.25s ease-out; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Custom Scrollbar yang halus */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
</style>