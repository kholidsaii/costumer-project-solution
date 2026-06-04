<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const BASE_URL = 'http://localhost:8000';
const getToken = () => localStorage.getItem('access_token');
const axiosConfig = () => ({
  headers: { Authorization: `Bearer ${getToken()}`, Accept: 'application/json' }
});

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

const categories = ['Software', 'Templates', 'Integrations', 'Services'];

const defaultForm = {
  id: null, name: '', description: '', overview: '', price: 0, category: 'Software',
  is_active: true, features: [], faqs: [], changelogs: []
};
const formData = ref(JSON.parse(JSON.stringify(defaultForm)));

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`${BASE_URL}/api/admin/products`, axiosConfig()); 
    products.value = Array.isArray(response.data) ? response.data : (response.data.data || []);
  } catch (error) {
    products.value = [];
  } finally { loading.value = false; }
};

onMounted(fetchProducts);

const addFeature = () => formData.value.features.push({ title: '', description: '' });
const removeFeature = (index: any) => formData.value.features.splice(index, 1);
const addFaq = () => formData.value.faqs.push({ question: '', answer: '' });
const removeFaq = (index: any) => formData.value.faqs.splice(index, 1);
const addChangelog = () => formData.value.changelogs.push({ version: '', release_date: '', changes: '' });
const removeChangelog = (index: any) => formData.value.changelogs.splice(index, 1);

// ===== LOGIKA DRAG & DROP GAMBAR UTAMA =====
const onDragOverMain = (e: DragEvent) => { e.preventDefault(); isDraggingMain.value = true; };
const onDragLeaveMain = () => { isDraggingMain.value = false; };
const onDropMain = (e: DragEvent) => {
  e.preventDefault(); isDraggingMain.value = false;
  if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) processMainImage(e.dataTransfer.files[0]);
};
const onFileSelectMain = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) processMainImage(target.files[0]);
};

const processMainImage = (file: File) => {
  if (file.type.startsWith('image/')) {
    imageFile.value = file;
    if (mainImagePreview.value) URL.revokeObjectURL(mainImagePreview.value);
    mainImagePreview.value = URL.createObjectURL(file);
    removeMainImageFlag.value = false;
  }
};

const removeMainImage = () => {
  if (mainImagePreview.value) URL.revokeObjectURL(mainImagePreview.value);
  mainImagePreview.value = null;
  imageFile.value = null;
  if (existingMainImage.value) {
    removeMainImageFlag.value = true;
    existingMainImage.value = null;
  }
};

// ===== LOGIKA DRAG & DROP SCREENSHOTS =====
const onDragOverSS = (e: DragEvent) => { e.preventDefault(); isDraggingSS.value = true; };
const onDragLeaveSS = () => { isDraggingSS.value = false; };
const onDropSS = (e: DragEvent) => {
  e.preventDefault(); isDraggingSS.value = false;
  if (e.dataTransfer?.files) processScreenshots(e.dataTransfer.files);
};
const onFileSelectSS = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files) processScreenshots(target.files);
};

const processScreenshots = (files: FileList) => {
  Array.from(files).forEach(file => {
    if (file.type.startsWith('image/')) {
      newScreenshots.value.push({ file: file, preview: URL.createObjectURL(file) });
    }
  });
};

const removeExistingScreenshot = (index: number) => {
  deletedScreenshots.value.push(existingScreenshots.value[index].id);
  existingScreenshots.value.splice(index, 1);
};

const removeNewScreenshot = (index: number) => {
  URL.revokeObjectURL(newScreenshots.value[index].preview); 
  newScreenshots.value.splice(index, 1);
};

// ===== MODAL CONTROLS =====
const openAddModal = () => {
  isEditMode.value = false;
  
  // Reset Main Image
  existingMainImage.value = null;
  if (mainImagePreview.value) URL.revokeObjectURL(mainImagePreview.value);
  mainImagePreview.value = null;
  removeMainImageFlag.value = false;
  imageFile.value = null;

  // Reset Screenshots
  existingScreenshots.value = [];
  deletedScreenshots.value = [];
  newScreenshots.value.forEach(s => URL.revokeObjectURL(s.preview));
  newScreenshots.value = [];
  
  formData.value = JSON.parse(JSON.stringify(defaultForm));
  isModalOpen.value = true;
};

const openEditModal = async (product: any) => {
  isEditMode.value = true;
  
  // Reset Main Image
  if (mainImagePreview.value) URL.revokeObjectURL(mainImagePreview.value);
  mainImagePreview.value = null;
  removeMainImageFlag.value = false;
  imageFile.value = null;

  // Reset Screenshots
  deletedScreenshots.value = [];
  newScreenshots.value.forEach(s => URL.revokeObjectURL(s.preview));
  newScreenshots.value = [];

  try {
    const response = await axios.get(`${BASE_URL}/api/products/${product.slug}`);
    const detail = response.data;
    formData.value = {
      id: detail.id, name: detail.name, description: detail.description, overview: detail.overview,
      price: detail.price, category: detail.category, is_active: detail.is_active,
      features: detail.features || [], faqs: detail.faqs || [], changelogs: detail.changelogs || []
    };
    
    // Load existing images
    existingMainImage.value = detail.image || null;
    existingScreenshots.value = detail.screenshots || [];
    
    isModalOpen.value = true;
  } catch (error) { alert('Gagal mengambil detail produk.'); }
};

const closeModal = () => {
  if (mainImagePreview.value) URL.revokeObjectURL(mainImagePreview.value);
  newScreenshots.value.forEach(s => URL.revokeObjectURL(s.preview)); 
  isModalOpen.value = false;
};

// ===== SUBMIT FORM =====
const handleSubmit = async () => {
  submitting.value = true;
  try {
    const data = new FormData();
    data.append('name', formData.value.name);
    data.append('description', formData.value.description);
    data.append('overview', formData.value.overview);
    data.append('price', formData.value.price);
    data.append('category', formData.value.category);
    data.append('is_active', formData.value.is_active ? '1' : '0');
    
    // Append Main Image & Flag
    if (imageFile.value) data.append('image', imageFile.value);
    if (removeMainImageFlag.value) data.append('remove_main_image', 'true');

    // Append File Screenshots Baru
    newScreenshots.value.forEach((item, index) => {
      data.append(`screenshots[${index}]`, item.file);
    });

    // Append ID Screenshots Lama yg Dihapus
    deletedScreenshots.value.forEach((id, index) => {
      data.append(`deleted_screenshots[${index}]`, id.toString());
    });

    // Append Text Arrays
    formData.value.features.forEach((feat: any, i: number) => {
      data.append(`features[${i}][title]`, feat.title);
      data.append(`features[${i}][description]`, feat.description);
    });
    formData.value.faqs.forEach((faq: any, i: number) => {
      data.append(`faqs[${i}][question]`, faq.question);
      data.append(`faqs[${i}][answer]`, faq.answer);
    });
    formData.value.changelogs.forEach((log: any, i: number) => {
      data.append(`changelogs[${i}][version]`, log.version);
      data.append(`changelogs[${i}][release_date]`, log.release_date);
      data.append(`changelogs[${i}][changes]`, log.changes);
    });

    const multipartConfig = {
      headers: { ...axiosConfig().headers, 'Content-Type': 'multipart/form-data' }
    };

    if (isEditMode.value) {
      data.append('_method', 'PUT');
      await axios.post(`${BASE_URL}/api/admin/products/${formData.value.id}`, data, multipartConfig);
    } else {
      await axios.post(`${BASE_URL}/api/admin/products`, data, multipartConfig);
    }
    closeModal();
    fetchProducts();
  } catch (error) {
    alert('Terjadi kesalahan saat menyimpan data.');
  } finally {
    submitting.value = false;
  }
};

const deleteProduct = async (id: number) => {
  if (!confirm('Hapus produk ini secara permanen?')) return;
  try {
    await axios.delete(`${BASE_URL}/api/admin/products/${id}`, axiosConfig());
    fetchProducts();
  } catch (error) { alert('Gagal menghapus produk.'); }
};

const formatPrice = (price: any) => {
  const numericPrice = Number(price);
  if (isNaN(numericPrice) || !price) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(numericPrice);
};
</script>

<template>
  <div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-800">Manajemen Produk</h1>
        <p class="text-xs font-bold text-slate-500 mt-1">Kelola katalog produk, detail, fitur, dan harga.</p>
      </div>
      <button @click="openAddModal" class="bg-[#B48440] text-white px-4 py-2.5 rounded-xl text-sm font-black hover:bg-[#91672D] transition shadow-sm flex items-center gap-2">
        <span>+</span> Tambah Produk Baru
      </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-black">
              <th class="p-4">Info Produk</th>
              <th class="p-4">Kategori</th>
              <th class="p-4">Harga</th>
              <th class="p-4">Status</th>
              <th class="p-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-if="loading"><td colspan="5" class="p-8 text-center text-slate-400 font-bold">Memuat data...</td></tr>
            <tr v-else-if="products.length === 0"><td colspan="5" class="p-8 text-center text-slate-400 font-bold">Belum ada produk terdaftar.</td></tr>
            <tr v-else v-for="product in products" :key="product.id" class="border-b border-slate-100 hover:bg-slate-50 transition">
              <td class="p-4 flex items-center gap-3">
                <img v-if="product.image" :src="`${BASE_URL}/storage/${product.image}`" class="w-10 h-10 rounded-lg object-cover shadow-sm border border-slate-200" />
                <div v-else class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-[10px] font-black text-slate-400 border border-slate-200">IMG</div>
                <div>
                  <p class="font-black text-slate-800">{{ product.name }}</p>
                  <p class="text-xs text-slate-500 line-clamp-1 mt-0.5 max-w-xs">{{ product.description }}</p>
                </div>
              </td>
              <td class="p-4 font-bold text-slate-600">{{ product.category }}</td>
              <td class="p-4 font-black text-indigo-600">{{ formatPrice(product.price) }}</td>
              <td class="p-4">
                <span :class="['px-2 py-1 rounded text-[10px] font-black uppercase', product.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                  {{ product.is_active ? 'Aktif' : 'Draft' }}
                </span>
              </td>
              <td class="p-4 text-right space-x-2">
                <button @click="openEditModal(product)" class="text-blue-500 hover:text-blue-700 font-bold text-xs bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 transition">Edit Detail</button>
                <button @click="deleteProduct(product.id)" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 px-3 py-1.5 rounded-lg border border-red-100 transition">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex justify-center items-center p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
          <h2 class="text-lg font-black text-slate-800">{{ isEditMode ? 'Edit Produk' : 'Tambah Produk Baru' }}</h2>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 text-xl font-black">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 bg-slate-50/50">
          <form @submit.prevent="handleSubmit" id="productForm" class="space-y-8">
            
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
              <h3 class="font-black text-slate-800 mb-4 border-b border-slate-100 pb-2">Informasi Dasar</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-xs font-bold text-slate-500 mb-1">Nama Produk *</label>
                  <input v-model="formData.name" type="text" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#B48440]" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Kategori *</label>
                  <select v-model="formData.category" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#B48440] bg-white">
                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Harga (IDR) *</label>
                  <input v-model="formData.price" type="number" required min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#B48440]" />
                </div>
                
                <div class="md:col-span-2 mt-2">
                  <label class="block text-xs font-bold text-slate-500 mb-2">Gambar Produk Utama (Thumbnail)</label>
                  
                  <div v-if="!existingMainImage && !mainImagePreview"
                    @dragover="onDragOverMain" 
                    @dragleave="onDragLeaveMain" 
                    @drop="onDropMain"
                    :class="['border-2 border-dashed rounded-xl p-6 text-center transition-colors flex flex-col items-center justify-center', 
                    isDraggingMain ? 'border-[#B48440] bg-yellow-50/30' : 'border-slate-300 bg-slate-50 hover:bg-slate-100']"
                  >
                    <div class="text-3xl mb-2 text-slate-400">🖼️</div>
                    <p class="text-sm font-bold text-slate-600 mb-1">Tarik dan lepas satu gambar ke sini</p>
                    <p class="text-xs text-slate-400 mb-3">atau</p>
                    <label class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-xs font-black cursor-pointer hover:bg-slate-50 transition shadow-sm">
                      Pilih File
                      <input type="file" accept="image/*" class="hidden" @change="onFileSelectMain" />
                    </label>
                  </div>

                  <div v-else class="relative group w-full sm:w-1/2 md:w-1/3 aspect-video bg-slate-100 rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                    <img :src="mainImagePreview ? mainImagePreview : `${BASE_URL}/storage/${existingMainImage}`" class="w-full h-full object-cover" />
                    <div v-if="mainImagePreview" class="absolute top-2 left-2 bg-emerald-500 text-white text-[10px] font-black px-2 py-1 rounded shadow">BARU</div>
                    
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <button type="button" @click="removeMainImage" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-black hover:bg-red-600 shadow-lg flex items-center gap-1">
                        &times; Hapus
                      </button>
                    </div>
                  </div>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs font-bold text-slate-500 mb-1">Deskripsi Singkat (Card Preview) *</label>
                  <textarea v-model="formData.description" required rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#B48440]"></textarea>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs font-bold text-slate-500 mb-1">Overview Lengkap (Detail Page) *</label>
                  <textarea v-model="formData.overview" required rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#B48440]"></textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                  <input type="checkbox" v-model="formData.is_active" id="isActive" class="w-4 h-4 accent-[#B48440]" />
                  <label for="isActive" class="text-sm font-bold text-slate-700 cursor-pointer">Publikasikan Produk Ini (Aktif)</label>
                </div>
              </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
              <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                <div>
                  <h3 class="font-black text-slate-800">Screenshots Produk</h3>
                  <p class="text-[11px] text-slate-500 font-bold">Galeri gambar pendukung (Bisa pilih banyak gambar sekaligus).</p>
                </div>
              </div>
              
              <div 
                @dragover="onDragOverSS" 
                @dragleave="onDragLeaveSS" 
                @drop="onDropSS"
                :class="['border-2 border-dashed rounded-xl p-8 text-center transition-colors mb-4 flex flex-col items-center justify-center', 
                isDraggingSS ? 'border-[#B48440] bg-yellow-50/30' : 'border-slate-300 bg-slate-50 hover:bg-slate-100']"
              >
                <div class="text-3xl mb-2 text-slate-400">🖼️</div>
                <p class="text-sm font-bold text-slate-600 mb-1">Tarik dan lepas gambar ke sini</p>
                <p class="text-xs text-slate-400 mb-4">atau klik tombol di bawah untuk memilih file</p>
                <label class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-xs font-black cursor-pointer hover:bg-slate-50 transition shadow-sm">
                  Pilih Gambar
                  <input type="file" multiple accept="image/*" class="hidden" @change="onFileSelectSS" />
                </label>
              </div>

              <div v-if="existingScreenshots.length > 0 || newScreenshots.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                
                <div v-for="(img, idx) in existingScreenshots" :key="'old-'+idx" class="relative group aspect-video bg-slate-100 rounded-lg overflow-hidden border border-slate-200 shadow-sm">
                  <img :src="`${BASE_URL}/storage/${img.image_path}`" class="w-full h-full object-cover" />
                  <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button type="button" @click="removeExistingScreenshot(idx)" class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold hover:bg-red-600 shadow-lg">&times;</button>
                  </div>
                </div>

                <div v-for="(img, idx) in newScreenshots" :key="'new-'+idx" class="relative group aspect-video bg-slate-100 rounded-lg overflow-hidden border-2 border-emerald-400 border-dashed shadow-sm">
                  <img :src="img.preview" class="w-full h-full object-cover" />
                  <div class="absolute top-1 left-1 bg-emerald-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">BARU</div>
                  <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button type="button" @click="removeNewScreenshot(idx)" class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold hover:bg-red-600 shadow-lg">&times;</button>
                  </div>
                </div>

              </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
              <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                <h3 class="font-black text-slate-800">Fitur Produk (Features)</h3>
                <button type="button" @click="addFeature" class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100">+ Tambah Fitur</button>
              </div>
              <div v-for="(feat, index) in formData.features" :key="'feat-'+index" class="flex gap-3 items-start mb-3 p-3 bg-slate-50 border border-slate-100 rounded-lg">
                <div class="flex-1 space-y-2">
                  <input v-model="feat.title" placeholder="Judul Fitur" required class="w-full border border-slate-200 rounded px-2 py-1.5 text-sm" />
                  <input v-model="feat.description" placeholder="Deskripsi Singkat" required class="w-full border border-slate-200 rounded px-2 py-1.5 text-sm" />
                </div>
                <button type="button" @click="removeFeature(index)" class="text-red-500 font-black px-2 py-1 bg-red-50 rounded border border-red-100">&times;</button>
              </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
              <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                <h3 class="font-black text-slate-800">Tanya Jawab (FAQ)</h3>
                <button type="button" @click="addFaq" class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100">+ Tambah FAQ</button>
              </div>
              <div v-for="(faq, index) in formData.faqs" :key="'faq-'+index" class="flex gap-3 items-start mb-3 p-3 bg-slate-50 border border-slate-100 rounded-lg">
                <div class="flex-1 space-y-2">
                  <input v-model="faq.question" placeholder="Pertanyaan (Q)" required class="w-full border border-slate-200 rounded px-2 py-1.5 text-sm font-bold" />
                  <textarea v-model="faq.answer" placeholder="Jawaban (A)" required rows="2" class="w-full border border-slate-200 rounded px-2 py-1.5 text-sm"></textarea>
                </div>
                <button type="button" @click="removeFaq(index)" class="text-red-500 font-black px-2 py-1 bg-red-50 rounded border border-red-100">&times;</button>
              </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
              <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                <h3 class="font-black text-slate-800">Changelog / Update</h3>
                <button type="button" @click="addChangelog" class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100">+ Tambah Log</button>
              </div>
              <div v-for="(log, index) in formData.changelogs" :key="'log-'+index" class="flex gap-3 items-start mb-3 p-3 bg-slate-50 border border-slate-100 rounded-lg">
                <div class="flex-1 grid grid-cols-2 gap-2">
                  <input v-model="log.version" placeholder="Versi (cth: 1.0.1)" required class="w-full border border-slate-200 rounded px-2 py-1.5 text-sm" />
                  <input v-model="log.release_date" type="date" required class="w-full border border-slate-200 rounded px-2 py-1.5 text-sm" />
                  <textarea v-model="log.changes" placeholder="Detail Perubahan" required rows="2" class="col-span-2 w-full border border-slate-200 rounded px-2 py-1.5 text-sm"></textarea>
                </div>
                <button type="button" @click="removeChangelog(index)" class="text-red-500 font-black px-2 py-1 bg-red-50 rounded border border-red-100">&times;</button>
              </div>
            </div>

          </form>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-white flex justify-end gap-3">
          <button @click="closeModal" type="button" class="px-4 py-2 text-sm font-bold text-slate-500 bg-slate-100 rounded-lg hover:bg-slate-200 transition">Batal</button>
          <button type="submit" form="productForm" :disabled="submitting" class="px-6 py-2 text-sm font-black text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition disabled:opacity-50 flex items-center gap-2">
            {{ submitting ? 'Menyimpan...' : (isEditMode ? 'Simpan Perubahan' : 'Buat Produk') }}
          </button>
        </div>

      </div>
    </div>
  </div>
</template>