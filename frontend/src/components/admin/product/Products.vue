<script setup lang="ts">
import { ref, onMounted } from 'vue';
// Menggunakan instance Axios custom (axios.ts)
// Pastikan path import di bawah ini disesuaikan dengan letak folder API Anda
import api from '../../../api/axios'; 

const BASE_URL = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000';

const products = ref<any[]>([]);
const loading = ref(true);
const isModalOpen = ref(false);
const isEditMode = ref(false);
const submitting = ref(false);

// --- STATE GAMBAR UTAMA ---
const imageFile = ref<File | null>(null);
const isDraggingMain = ref(false);
const mainImagePreview = ref<string | null>(null);
const existingMainImage = ref<string | null>(null);
const removeMainImageFlag = ref(false);

// --- STATE SCREENSHOTS ---
const screenshotFiles = ref<File[]>([]);
const isDraggingSS = ref(false);
const existingScreenshots = ref<any[]>([]); 
const deletedScreenshots = ref<number[]>([]); 
const newScreenshots = ref<{ file: File, preview: string }[]>([]); 

// 1. Tambahkan Interface (KTP) ini agar TypeScript tahu isi form-nya
interface FormState {
  id: number | null;
  name: string;
  description: string;
  overview: string;
  price: number;
  type: string;
  access_tier: string;
  quantity: number;
  is_active: boolean;
  features: any[];
  faqs: any[];
  changelogs: any[];
}

// 2. Terapkan Interface tersebut ke defaultForm
const defaultForm: FormState = {
  id: null, name: '', description: '', overview: '', price: 0, 
  type: '', 
  access_tier: '', 
  quantity: 0, 
  is_active: true, 
  features: [], faqs: [], changelogs: []
};
const form = ref<FormState>(JSON.parse(JSON.stringify(defaultForm)));

const activeTab = ref('Dasar');
const tabs = ['Dasar', 'Media', 'Fitur', 'FAQ', 'Pembaruan'];

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await api.get('/admin/products');
    products.value = response.data;
  } catch (error) {
    console.error('Gagal memuat produk:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProducts);

const handleTypeChange = () => {
  if (form.value.type === 'Software') {
    form.value.access_tier = 'gold';
    form.value.quantity = 0;
  } else if (form.value.type === 'Digital') {
    form.value.access_tier = 'free';
    form.value.quantity = 0;
  } else if (form.value.type === 'Fisik') {
    form.value.access_tier = 'all';
    form.value.quantity = 1;
  }
};

const openModal = () => {
  isModalOpen.value = true;
  isEditMode.value = false;
  form.value = JSON.parse(JSON.stringify(defaultForm));
  resetImageState();
  activeTab.value = 'Dasar';
};

const closeModal = () => {
  isModalOpen.value = false;
  resetImageState();
};

const resetImageState = () => {
  imageFile.value = null; mainImagePreview.value = null; existingMainImage.value = null; removeMainImageFlag.value = false;
  screenshotFiles.value = []; existingScreenshots.value = []; deletedScreenshots.value = []; newScreenshots.value = [];
};

const editProduct = (product: any) => {
  isModalOpen.value = true;
  isEditMode.value = true;
  form.value = JSON.parse(JSON.stringify(product));
  
  if(!form.value.type) form.value.type = product.category || '';
  
  activeTab.value = 'Dasar';
  resetImageState();

  if (product.image) existingMainImage.value = `${BASE_URL}/storage/${product.image}`;
  if (product.screenshots) existingScreenshots.value = [...product.screenshots];
};

const deleteProduct = async (id: number) => {
  if (!confirm('Hapus produk ini?')) return;
  try {
    await api.delete(`/admin/products/${id}`);
    fetchProducts();
  } catch (error) { console.error('Gagal menghapus produk', error); }
};

const handleMainImageUpload = (event: any) => {
  const file = event.target.files[0];
  if (file) {
    imageFile.value = file;
    mainImagePreview.value = URL.createObjectURL(file);
    removeMainImageFlag.value = false;
  }
};

const removeMainImage = () => {
  imageFile.value = null;
  mainImagePreview.value = null;
  existingMainImage.value = null;
  removeMainImageFlag.value = true;
};

const handleScreenshotsUpload = (event: any) => {
  const files = Array.from(event.target.files) as File[];
  files.forEach(file => {
    screenshotFiles.value.push(file);
    newScreenshots.value.push({ file, preview: URL.createObjectURL(file) });
  });
};

// Mengubah parameter 'index' menjadi 'idx' agar lebih aman
const removeNewScreenshot = (idx: number) => {
  newScreenshots.value.splice(idx, 1);
  screenshotFiles.value.splice(idx, 1);
};

const removeExistingScreenshot = (id: number, idx: number) => {
  deletedScreenshots.value.push(id);
  existingScreenshots.value.splice(idx, 1);
};

const submitProduct = async () => {
  submitting.value = true;
  const formData = new FormData();
  
  // Perbaiki bagian ini dengan TypeScript type assertion (as keyof FormState)
  (Object.keys(form.value) as Array<keyof FormState>).forEach(key => {
    if (['features', 'faqs', 'changelogs'].includes(key as string)) {
      // Pastikan TypeScript tahu ini adalah array sebelum menggunakan forEach
      const arrayValue = form.value[key];
      if (Array.isArray(arrayValue)) {
        arrayValue.forEach((item: any, i: number) => {
          Object.keys(item).forEach(subKey => formData.append(`${key}[${i}][${subKey}]`, item[subKey]));
        });
      }
    } else {
      // Pastikan nilainya string/blob/null sebelum di-append
      const value = form.value[key];
      if (value !== null && value !== undefined) {
        formData.append(key as string, value.toString());
      }
    }
  });

  // Pastikan property 'category' ditangkap backend (berasal dari form.type)
  formData.append('category', form.value.type);

  if (imageFile.value) formData.append('image', imageFile.value);
  if (removeMainImageFlag.value) formData.append('remove_image', '1');

  screenshotFiles.value.forEach(file => formData.append('screenshots[]', file));
  deletedScreenshots.value.forEach(id => formData.append('deleted_screenshots[]', id.toString()));

  try {
    if (isEditMode.value && form.value.id) {
      formData.append('_method', 'PUT');
      await api.post(`/admin/products/${form.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      await api.post(`/admin/products`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }
    closeModal();
    fetchProducts();
  } catch (error) {
    console.error('Gagal menyimpan:', error);
  } finally {
    submitting.value = false;
  }
};

// Array Helpers (Parameter diubah menjadi idx)
const addFeature = () => form.value.features.push({ title: '', description: '' });
const removeFeature = (idx: number) => form.value.features.splice(idx, 1);

const addFaq = () => form.value.faqs.push({ question: '', answer: '' });
const removeFaq = (idx: number) => form.value.faqs.splice(idx, 1);

const addChangelog = () => form.value.changelogs.push({ version: '', release_date: '', changes: '' });
const removeChangelog = (idx: number) => form.value.changelogs.splice(idx, 1);
</script>

<template>
  <div class="p-6 md:p-10 flex-1 max-h-screen overflow-y-auto">
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-800">Manajemen Produk</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola dan update katalog produk Anda di sini.</p>
      </div>
      <button @click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
        <span class="text-lg">+</span> Tambah Produk
      </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
              <th class="p-4 font-black">Info Produk</th>
              <th class="p-4 font-black">Jenis & Harga</th>
              <th class="p-4 font-black">Akses</th>
              <th class="p-4 font-black text-center">Status</th>
              <th class="p-4 font-black text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading"><td colspan="5" class="p-8 text-center text-slate-400">Memuat data produk...</td></tr>
            <tr v-else-if="products.length === 0"><td colspan="5" class="p-8 text-center text-slate-400">Belum ada produk.</td></tr>
            <tr v-else v-for="product in products" :key="product.id" class="hover:bg-slate-50/50 transition">
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex-shrink-0 overflow-hidden">
                    <img v-if="product.image" :src="`${BASE_URL}/storage/${product.image}`" class="w-full h-full object-cover"/>
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold">Img</div>
                  </div>
                  <div>
                    <div class="font-bold text-slate-800 text-sm line-clamp-1">{{ product.name }}</div>
                    <div class="text-xs text-slate-500 line-clamp-1">{{ product.description }}</div>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <div class="text-sm font-bold text-slate-800">{{ product.category || product.type }}</div>
                <div class="text-[11px] text-slate-500">Rp {{ product.price.toLocaleString('id-ID') }}</div>
              </td>
              <td class="p-4">
                <span v-if="product.access_tier === 'gold'" class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md text-[10px] font-black uppercase">Gold</span>
                <span v-else-if="product.access_tier === 'silver'" class="px-2 py-1 bg-slate-200 text-slate-700 rounded-md text-[10px] font-black uppercase">Silver</span>
                <span v-else-if="product.access_tier === 'free'" class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-[10px] font-black uppercase">Free</span>
                <span v-else class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-md text-[10px] font-black uppercase">Semua (All)</span>
              </td>
              <td class="p-4 text-center">
                <span :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 rounded-full text-xs font-bold">
                  {{ product.is_active ? 'Aktif' : 'Draft' }}
                </span>
              </td>
              <td class="p-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="editProduct(product)" class="p-2 text-blue-500 bg-blue-50 hover:bg-blue-100 rounded-lg transition" title="Edit">✏️</button>
                  <button @click="deleteProduct(product.id)" class="p-2 text-red-500 bg-red-50 hover:bg-red-100 rounded-lg transition" title="Hapus">🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fade-in">
        
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col gap-4">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-black text-slate-800">{{ isEditMode ? 'Edit Produk' : 'Tambah Produk Baru' }}</h2>
            <button @click="closeModal" class="text-slate-400 hover:text-red-500 transition text-xl font-bold">&times;</button>
          </div>
          
          <div>
            <label class="block text-[12px] font-black text-slate-800 mb-1">Pilih Jenis Produk <span class="text-red-500">*</span></label>
            <select v-model="form.type" @change="handleTypeChange" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition shadow-sm">
              <option value="" disabled>-- Silakan Pilih Jenis Produk --</option>
              <option value="Software">💻 Software</option>
              <option value="Digital">📄 Produk Digital</option>
              <option value="Fisik">📦 Produk Fisik</option>
            </select>
            <p v-if="!form.type" class="text-[11px] text-red-500 mt-1 font-bold">Pilih jenis produk terlebih dahulu untuk memunculkan form isian.</p>
          </div>
        </div>

        <div v-if="form.type" class="flex flex-1 overflow-hidden">
          
          <div class="w-48 bg-slate-50 border-r border-slate-200 flex flex-col">
            <button v-for="tab in tabs" :key="tab" @click="activeTab = tab" 
              :class="activeTab === tab ? 'bg-white border-l-4 border-blue-600 text-blue-700 font-black' : 'text-slate-600 font-bold hover:bg-slate-100'"
              class="px-4 py-3 text-left text-sm transition-colors border-b border-slate-100 last:border-0">
              {{ tab }}
            </button>
          </div>

          <form id="productForm" @submit.prevent="submitProduct" class="flex-1 overflow-y-auto p-6 bg-white space-y-6 relative">
            
            <div v-show="activeTab === 'Dasar'" class="space-y-4 animate-fade-in">
              <div>
                <label class="block text-[12px] font-black text-slate-800 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input v-model="form.name" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" placeholder="Contoh: Aplikasi Keuangan Pro" />
              </div>
              <div>
                <label class="block text-[12px] font-black text-slate-800 mb-1">Deskripsi Singkat <span class="text-red-500">*</span></label>
                <textarea v-model="form.description" required rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" placeholder="Deskripsi singkat produk..."></textarea>
              </div>
              
              <div class="p-4 rounded-lg bg-blue-50/50 border border-blue-100 space-y-4">
                
                <div v-if="form.type === 'Software'">
                  <label class="block text-[12px] font-black text-slate-800 mb-1">Akses Member (Otomatis)</label>
                  <input type="text" disabled value="Gold Member Khusus" class="w-full border border-slate-200 bg-slate-100 text-slate-500 rounded-lg px-3 py-2 text-sm font-bold" />
                  <p class="text-[10px] text-slate-500 mt-1">Hanya pengguna Tier Gold yang dapat mengakses produk ini.</p>
                </div>

                <div v-if="form.type === 'Digital'">
                  <label class="block text-[12px] font-black text-slate-800 mb-1">Akses Member <span class="text-red-500">*</span></label>
                  <select v-model="form.access_tier" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 bg-white">
                    <option value="free">Free Tier</option>
                    <option value="silver">Silver Tier</option>
                  </select>
                  <p class="text-[10px] text-slate-500 mt-1">Limit jumlah unduhan diatur pada halaman dashboard customer.</p>
                </div>

                <div v-if="form.type === 'Fisik'" class="flex gap-4">
                  <div class="flex-1">
                    <label class="block text-[12px] font-black text-slate-800 mb-1">Quantity / Stok <span class="text-red-500">*</span></label>
                    <input v-model="form.quantity" type="number" min="0" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 bg-white" placeholder="Jumlah stok..." />
                  </div>
                  <div class="flex-1">
                    <label class="block text-[12px] font-black text-slate-800 mb-1">Akses Member (Otomatis)</label>
                    <input type="text" disabled value="Semua Member (Tergantung Stok)" class="w-full border border-slate-200 bg-slate-100 text-slate-500 rounded-lg px-3 py-2 text-sm font-bold" />
                  </div>
                </div>

              </div>

              <div class="flex gap-4">
                <div class="flex-1">
                  <label class="block text-[12px] font-black text-slate-800 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                  <input v-model="form.price" type="number" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" />
                </div>
                <div class="w-32">
                  <label class="block text-[12px] font-black text-slate-800 mb-1">Status Produk</label>
                  <select v-model="form.is_active" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option :value="true">Aktif</option>
                    <option :value="false">Draft</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="block text-[12px] font-black text-slate-800 mb-1">Overview Lengkap</label>
                <textarea v-model="form.overview" rows="4" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" placeholder="Penjelasan detail tentang produk..."></textarea>
              </div>
            </div>

            <div v-show="activeTab === 'Media'" class="space-y-6 animate-fade-in">
              <div>
                <label class="block text-[12px] font-black text-slate-800 mb-2">Gambar Utama / Cover</label>
                <div v-if="!mainImagePreview && !existingMainImage" 
                     @dragover.prevent="isDraggingMain = true" 
                     @dragleave.prevent="isDraggingMain = false" 
                     @drop.prevent="handleMainImageUpload"
                     :class="['border-2 border-dashed rounded-xl p-8 text-center transition-colors', isDraggingMain ? 'border-blue-500 bg-blue-50' : 'border-slate-300 hover:border-slate-400']">
                  <input type="file" id="mainImage" accept="image/*" class="hidden" @change="handleMainImageUpload" />
                  <label for="mainImage" class="cursor-pointer flex flex-col items-center gap-2">
                    <span class="text-3xl">📸</span>
                    <span class="text-sm font-bold text-blue-600 hover:text-blue-700">Klik untuk upload</span>
                    <span class="text-xs text-slate-500">atau drag and drop file di sini (PNG, JPG)</span>
                  </label>
                </div>
                <div v-else class="relative w-full max-w-sm rounded-xl overflow-hidden border border-slate-200 group">
                  <img :src="mainImagePreview || existingMainImage!" class="w-full h-auto object-cover" />
                  <button type="button" @click="removeMainImage" class="absolute top-2 right-2 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity font-bold shadow-md">&times;</button>
                </div>
              </div>

              <hr class="border-slate-100" />

              <div>
                <label class="block text-[12px] font-black text-slate-800 mb-2">Galeri / Screenshots Tambahan</label>
                <div @dragover.prevent="isDraggingSS = true" @dragleave.prevent="isDraggingSS = false" @drop.prevent="handleScreenshotsUpload"
                     :class="['border-2 border-dashed rounded-xl p-6 text-center transition-colors mb-4', isDraggingSS ? 'border-blue-500 bg-blue-50' : 'border-slate-300 hover:border-slate-400']">
                  <input type="file" id="screenshots" accept="image/*" multiple class="hidden" @change="handleScreenshotsUpload" />
                  <label for="screenshots" class="cursor-pointer text-sm font-bold text-blue-600 hover:text-blue-700 flex items-center justify-center gap-2">
                    <span class="text-xl">🖼️</span> Tambah Screenshots
                  </label>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div v-for="(ss, idx) in existingScreenshots" :key="ss.id" class="relative rounded-lg overflow-hidden border border-slate-200 group aspect-video">
                    <img :src="`${BASE_URL}/storage/${ss.image_path}`" class="w-full h-full object-cover" />
                    <button type="button" @click="removeExistingScreenshot(ss.id, idx)" class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold">&times;</button>
                  </div>
                  <div v-for="(ss, idx) in newScreenshots" :key="'new-ss-'+idx" class="relative rounded-lg overflow-hidden border border-blue-200 group aspect-video">
                    <img :src="ss.preview" class="w-full h-full object-cover opacity-80" />
                    <div class="absolute inset-0 border-2 border-blue-500 rounded-lg pointer-events-none"></div>
                    <button type="button" @click="removeNewScreenshot(idx)" class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold">&times;</button>
                  </div>
                </div>
              </div>
            </div>

            <div v-show="activeTab === 'Fitur'" class="space-y-4 animate-fade-in">
              <div class="flex justify-between items-center mb-2">
                <label class="text-[12px] font-black text-slate-800">Fitur Utama</label>
                <button type="button" @click="addFeature" class="text-xs font-bold text-blue-600 hover:text-blue-700">+ Tambah Fitur</button>
              </div>
              <div v-if="form.features.length === 0" class="text-center p-6 border border-dashed border-slate-300 rounded-xl text-slate-400 text-sm">Belum ada fitur ditambahkan.</div>
              <div v-for="(feature, idx) in form.features" :key="'feature-' + idx" class="flex gap-3 items-start bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div class="flex-1 space-y-2">
                  <input v-model="feature.title" placeholder="Nama Fitur (cth: Realtime Sync)" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" />
                  <textarea v-model="feature.description" placeholder="Deskripsi fitur..." required rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                </div>
                <button type="button" @click="removeFeature(idx)" class="text-red-500 font-black px-2 py-1 bg-red-50 rounded-lg border border-red-100 hover:bg-red-500 hover:text-white transition">&times;</button>
              </div>
            </div>

            <div v-show="activeTab === 'FAQ'" class="space-y-4 animate-fade-in">
              <div class="flex justify-between items-center mb-2">
                <label class="text-[12px] font-black text-slate-800">Tanya Jawab (FAQ)</label>
                <button type="button" @click="addFaq" class="text-xs font-bold text-blue-600 hover:text-blue-700">+ Tambah FAQ</button>
              </div>
              <div v-if="form.faqs.length === 0" class="text-center p-6 border border-dashed border-slate-300 rounded-xl text-slate-400 text-sm">Belum ada FAQ ditambahkan.</div>
              <div v-for="(faq, idx) in form.faqs" :key="'faq-' + idx" class="flex gap-3 items-start bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div class="flex-1 space-y-2">
                  <input v-model="faq.question" placeholder="Pertanyaan..." required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" />
                  <textarea v-model="faq.answer" placeholder="Jawaban..." required rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                </div>
                <button type="button" @click="removeFaq(idx)" class="text-red-500 font-black px-2 py-1 bg-red-50 rounded-lg border border-red-100 hover:bg-red-500 hover:text-white transition">&times;</button>
              </div>
            </div>

            <div v-show="activeTab === 'Pembaruan'" class="space-y-4 animate-fade-in">
              <div class="flex justify-between items-center mb-2">
                <label class="text-[12px] font-black text-slate-800">Riwayat Versi / Changelog</label>
                <button type="button" @click="addChangelog" class="text-xs font-bold text-blue-600 hover:text-blue-700">+ Tambah Versi</button>
              </div>
              <div v-if="form.changelogs.length === 0" class="text-center p-6 border border-dashed border-slate-300 rounded-xl text-slate-400 text-sm">Belum ada changelog ditambahkan.</div>
              <div v-for="(log, idx) in form.changelogs" :key="'log-' + idx" class="flex gap-3 items-start bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div class="flex-1 grid grid-cols-2 gap-2">
                  <input v-model="log.version" placeholder="Versi (cth: 1.0.1)" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" />
                  <input v-model="log.release_date" type="date" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500" />
                  <textarea v-model="log.changes" placeholder="Detail Perubahan (Pisahkan dengan baris baru)" required rows="2" class="col-span-2 w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                </div>
                <button type="button" @click="removeChangelog(idx)" class="text-red-500 font-black px-2 py-1 bg-red-50 rounded-lg border border-red-100 hover:bg-red-500 hover:text-white transition">&times;</button>
              </div>
            </div>
          </form>
        </div>

        <div v-else class="flex-1 flex flex-col items-center justify-center p-10 text-slate-400 bg-slate-50">
          <span class="text-6xl mb-4 opacity-50">📂</span>
          <p class="font-bold text-sm">Menunggu Pilihan Jenis Produk...</p>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-white flex justify-end gap-3 rounded-b-2xl">
          <button @click="closeModal" type="button" class="px-4 py-2 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
          <button type="submit" form="productForm" :disabled="submitting || !form.type" class="px-6 py-2 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
            {{ submitting ? 'Menyimpan...' : 'Simpan Produk' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
form::-webkit-scrollbar {
  width: 6px;
}
form::-webkit-scrollbar-track {
  background: transparent;
}
form::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}
</style>