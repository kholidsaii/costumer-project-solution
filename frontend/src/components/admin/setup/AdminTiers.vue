<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../../api/axios'; // Menggunakan custom axios instance seperti di file lain

const tiers = ref<any[]>([]);
const loading = ref(true);
const isModalOpen = ref(false);
const isEditMode = ref(false);
const submitting = ref(false);

const form = ref({
  id: null as number | null,
  name: '',
  description: '',
  price: 0
});

const fetchTiers = async () => {
  loading.value = true;
  try {
    const response = await api.get('/tiers');
    tiers.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil data tier:', error);
  } finally {
    loading.value = false;
  }
};

const openModal = (tier: any = null) => {
  isModalOpen.value = true;
  if (tier) {
    isEditMode.value = true;
    form.value = { id: tier.id, name: tier.name, description: tier.description, price: tier.price };
  } else {
    isEditMode.value = false;
    form.value = { id: null, name: '', description: '', price: 0 };
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  form.value = { id: null, name: '', description: '', price: 0 };
};

const submitTier = async () => {
  submitting.value = true;
  try {
    if (isEditMode.value) {
      await api.put(`/admin/tiers/${form.value.id}`, form.value);
    } else {
      await api.post(`/admin/tiers`, form.value);
    }
    closeModal();
    fetchTiers();
  } catch (error) {
    console.error('Gagal menyimpan tier:', error);
    alert('Gagal menyimpan data tier.');
  } finally {
    submitting.value = false;
  }
};

const deleteTier = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus tier ini? Pastikan tidak ada produk yang bergantung padanya.')) return;
  try {
    await api.delete(`/admin/tiers/${id}`);
    fetchTiers();
  } catch (error) {
    console.error('Gagal menghapus tier:', error);
    alert('Gagal menghapus tier.');
  }
};

onMounted(fetchTiers);
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <div>
        <h3 class="text-lg font-black text-slate-800">Master Setup Member Tier</h3>
        <p class="text-xs text-slate-500">Kelola level keanggotaan dan batas akses sistem.</p>
      </div>
      <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold text-xs transition flex items-center gap-2">
        <span>+</span> Tambah Tier
      </button>
    </div>

    <div v-if="loading" class="text-center py-10 text-xs font-bold text-slate-400">Memuat data master...</div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider text-xs">
            <th class="p-4">Nama Tier</th>
            <th class="p-4">Deskripsi</th>
            <th class="p-4">Biaya (IDR)</th>
            <th class="p-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <tr v-for="tier in tiers" :key="tier.id" class="hover:bg-slate-50/50 transition">
            <td class="p-4 font-bold">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="tier.slug === 'gold' ? 'bg-amber-400' : (tier.slug === 'silver' ? 'bg-slate-400' : 'bg-blue-400')"></span>
                {{ tier.name }}
              </div>
            </td>
            <td class="p-4 text-xs text-slate-500">{{ tier.description }}</td>
            <td class="p-4 font-black">Rp {{ Number(tier.price).toLocaleString('id-ID') }}</td>
            <td class="p-4 text-center">
              <div class="flex items-center justify-center gap-2">
                <button @click="openModal(tier)" class="p-2 text-blue-500 bg-blue-50 hover:bg-blue-100 rounded-lg transition" title="Edit">✏️</button>
                <button @click="deleteTier(tier.id)" class="p-2 text-red-500 bg-red-50 hover:bg-red-100 rounded-lg transition" title="Hapus">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-fade-in">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <h3 class="font-black text-sm text-slate-800">{{ isEditMode ? 'Ubah Master Tier' : 'Tambah Tier Baru' }}</h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 font-black text-lg">&times;</button>
        </div>
        <form @submit.prevent="submitTier" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Nama Tier <span class="text-red-500">*</span></label>
            <input type="text" v-model="form.name" required placeholder="Contoh: Platinum Member" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Biaya Keanggotaan (IDR) <span class="text-red-500">*</span></label>
            <input type="number" v-model="form.price" required min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-xs font-black text-slate-700 mb-1">Deskripsi Singkat</label>
            <textarea v-model="form.description" rows="3" placeholder="Fasilitas yang didapat..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
          </div>
          
          <div class="pt-4 border-t border-slate-100 flex justify-end gap-2 mt-6">
            <button type="button" @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-500 bg-slate-100 rounded-lg hover:bg-slate-200">Batal</button>
            <button type="submit" :disabled="submitting" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
              {{ submitting ? 'Menyimpan...' : 'Simpan Tier' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.2s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>