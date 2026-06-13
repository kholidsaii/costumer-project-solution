<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const BASE_URL = 'http://localhost:8000';
const products = ref<any[]>([]);
const loading = ref(true);

// Mengambil nama & tier user yang sedang login dari memori
const currentUserName = ref(localStorage.getItem('user_name') || '');
const userTierSlug = ref(localStorage.getItem('user_tier_slug') || 'free');

// --- FITUR BARU: MENGAMBIL DATA LIMIT DOWNLOAD DARI BACKEND ---
const currentUserData = ref<any>(null);

const fetchUserData = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const res = await axios.get(`${BASE_URL}/api/customer/me`, { headers: { Authorization: `Bearer ${token}` }});
    currentUserData.value = res.data;
  } catch (error) {
    console.error('Gagal mengambil data profil pelanggan', error);
  }
};

// Fungsi Cerdas Pengecek Limit Download
const isDownloadLimitReached = () => {
  if (!currentUserData.value) return false; // Abaikan saat masih loading
  const limit = currentUserData.value.tier?.digital_limit || 0;
  const count = currentUserData.value.digital_downloads_count || 0;
  return count >= limit && limit < 999999; // 999999 adalah kode untuk Unlimited
};

// --- STATE MODAL REVIEW ---
const isReviewModalOpen = ref(false);
const activeProductId = ref<number | null>(null);
const reviewForm = ref({ rating: 5, comment: '' });
const hoverRating = ref(0);
const submittingReview = ref(false);

// --- STATE MODAL DETAIL PRODUK ---
const isDetailModalOpen = ref(false);
const activeProduct = ref<any>(null);

// --- STATE CHECKOUT (Software & Physical) ---
const submittingOrder = ref(false);

// State Software
const isSoftwareModalOpen = ref(false);
const softwareForm = ref({ payment_method: 'BCA', proof: null as File | null });

// State Physical & RajaOngkir
const isPhysicalModalOpen = ref(false);
const provinces = ref<any[]>([]);
const cities = ref<any[]>([]);
const ongkirOptions = ref<any[]>([]);
const checkingOngkir = ref(false);

const physicalForm = ref({ 
  province_id: '', 
  city_id: '', 
  detail_address: '', 
  courier: '', 
  service: '', 
  cost: 0 
});

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`${BASE_URL}/api/products`);
    products.value = Array.isArray(response.data) ? response.data : (response.data.data || []);
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

// Panggil 2 fungsi sekaligus saat halaman dimuat
onMounted(() => {
  fetchProducts();
  fetchUserData();
});

const getUserReview = (product: any) => {
  if (!product || !product.reviews) return null;
  return product.reviews.find((r: any) => r.user?.name === currentUserName.value);
};

// --- FUNGSI RAJAONGKIR ---
const fetchProvinces = async () => {
  try {
    const token = localStorage.getItem('access_token');
    const res = await axios.get(`${BASE_URL}/api/customer/rajaongkir/provinces`, { headers: { Authorization: `Bearer ${token}` }});
    provinces.value = res.data.data || res.data.rajaongkir?.results || [];
  } catch (error: any) { 
    alert('GAGAL MEMUAT PROVINSI:\n' + (error.response?.data?.message || error.message));
  }
};

const fetchCities = async () => {
  physicalForm.value.city_id = '';
  cities.value = [];
  ongkirOptions.value = [];
  try {
    const token = localStorage.getItem('access_token');
    const res = await axios.get(`${BASE_URL}/api/customer/rajaongkir/cities/${physicalForm.value.province_id}`, { headers: { Authorization: `Bearer ${token}` }});
    cities.value = res.data.data || res.data.rajaongkir?.results || [];
  } catch (error: any) { 
    alert('GAGAL MEMUAT KOTA:\n' + (error.response?.data?.message || error.message));
  }
};

const handleCourierChange = () => {
  if (physicalForm.value.courier === 'toko') {
    physicalForm.value.service = 'Toko';
    physicalForm.value.cost = 0;
    ongkirOptions.value = [];
    checkingOngkir.value = false;
  } else {
    checkOngkir();
  }
};

const checkOngkir = async () => {
  if (!physicalForm.value.city_id || !physicalForm.value.courier || physicalForm.value.courier === 'toko') return;
  
  checkingOngkir.value = true;
  ongkirOptions.value = [];
  physicalForm.value.cost = 0;
  physicalForm.value.service = '';

  try {
    const token = localStorage.getItem('access_token');
    const res = await axios.post(`${BASE_URL}/api/customer/rajaongkir/cost`, {
      destination_city_id: physicalForm.value.city_id,
      courier: physicalForm.value.courier,
      weight: 1000 
    }, { headers: { Authorization: `Bearer ${token}` }});

    if (res.data.data && Array.isArray(res.data.data)) ongkirOptions.value = res.data.data;
    else if (res.data.rajaongkir?.results) ongkirOptions.value = res.data.rajaongkir.results[0].costs;
  } catch (error: any) {
    if (error.response && error.response.status === 404) alert(error.response.data.message);
    else alert('GAGAL MENGHITUNG ONGKIR:\n' + (error.response?.data?.message || error.message));
  } finally { checkingOngkir.value = false; }
};

const submitPhysicalOrder = async () => {
  if (physicalForm.value.courier !== 'toko' && physicalForm.value.cost === 0) {
      return alert('Silakan pilih layanan ongkir terlebih dahulu!');
  }
  submittingOrder.value = true;
  
  const selectedProv = provinces.value.find(p => (p.id || p.province_id) == physicalForm.value.province_id);
  const provName = selectedProv ? (selectedProv.name || selectedProv.province) : '';

  const selectedCity = cities.value.find(c => (c.id || c.city_id) == physicalForm.value.city_id);
  const cityName = selectedCity ? (selectedCity.name || selectedCity.city_name) : '';

  const fullAddress = `${physicalForm.value.detail_address}, ${cityName}, ${provName}`;
  const courierString = physicalForm.value.courier === 'toko' ? 'Pengiriman Toko' : `${physicalForm.value.courier.toUpperCase()} - ${physicalForm.value.service}`;

  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/customer/orders`, {
      product_id: activeProduct.value.id,
      product_type: 'physical',
      shipping_address: fullAddress,
      courier: courierString,
      shipping_cost: physicalForm.value.cost
    }, { headers: { Authorization: `Bearer ${token}` } });
    
    alert(physicalForm.value.courier === 'toko' ? 'Berhasil! Pesanan dibuat. Silakan tunggu Admin memasukkan harga ongkir.' : 'Pesanan Fisik berhasil dibuat!');
    isPhysicalModalOpen.value = false;
    router.push('/dashboard/customer/orders');
  } catch (error) { 
    alert('Gagal memproses pesanan fisik.'); 
  } finally { 
    submittingOrder.value = false; 
  }
};

// --- LOGIKA UTAMA SAAT TOMBOL PESAN DIKLIK ---
const handleOrderClick = async (product: any) => {
  activeProduct.value = product;

  // 1. DIGITAL
  if (product.product_type === 'digital') {
    try {
      const token = localStorage.getItem('access_token');
      const res = await axios.post(`${BASE_URL}/api/customer/products/${product.id}/download`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
      
      // Update sisa limit di tampilan agar gembok bisa langsung terkunci jika limit habis
      if (currentUserData.value && res.data.current_downloads !== undefined) {
         currentUserData.value.digital_downloads_count = res.data.current_downloads;
      }

      const fileUrl = res.data.download_url;
      const link = document.createElement('a');
      link.href = fileUrl;
      link.setAttribute('target', '_blank'); 
      link.setAttribute('download', ''); 
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      alert('Berhasil! File digital Anda sedang diunduh.');

    } catch (error: any) {
      alert(error.response?.data?.message || 'Gagal mengunduh produk digital. Pastikan limit Anda cukup.');
    }
  } 
  // 2. SOFTWARE
  else if (product.product_type === 'software') {
    if (!userTierSlug.value.includes('gold')) {
      alert('Maaf, produk Software eksklusif hanya untuk Gold Member. Silakan upgrade tier Anda terlebih dahulu.');
      return;
    }
    isDetailModalOpen.value = false; 
    isSoftwareModalOpen.value = true;
  }
  // 3. PHYSICAL
  else if (product.product_type === 'physical') {
    isDetailModalOpen.value = false; 
    isPhysicalModalOpen.value = true;
    fetchProvinces(); 
  } 
};

// --- SUBMIT SOFTWARE ORDER ---
const submitSoftwareOrder = async () => {
  if (!softwareForm.value.proof) return alert('Pilih file bukti transfer!');
  submittingOrder.value = true;
  
  const formData = new FormData();
  formData.append('product_id', activeProduct.value.id.toString());
  formData.append('product_type', 'software');
  formData.append('payment_method', softwareForm.value.payment_method);
  formData.append('payment_proof', softwareForm.value.proof);

  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/customer/orders`, formData, {
      headers: { 'Content-Type': 'multipart/form-data', Authorization: `Bearer ${token}` }
    });
    alert('Pesanan Software berhasil dikirim! Menunggu verifikasi manual dari admin.');
    isSoftwareModalOpen.value = false;
    router.push('/dashboard/customer/orders');
  } catch (error) {
    alert('Gagal mengirim pesanan software.');
  } finally {
    submittingOrder.value = false;
  }
};

const openDetailModal = async (product: any) => {
  try {
    const response = await axios.get(`${BASE_URL}/api/products/${product.slug || product.id}`);
    activeProduct.value = response.data;
    isDetailModalOpen.value = true;
  } catch (error) {
    alert('Gagal memuat detail produk.');
  }
};

const openReviewModal = (product: any) => {
  activeProductId.value = product.id;
  const existingReview = getUserReview(product);
  if (existingReview) {
    reviewForm.value = { rating: existingReview.rating, comment: existingReview.comment };
  } else {
    reviewForm.value = { rating: 5, comment: '' };
  }
  hoverRating.value = 0;
  isReviewModalOpen.value = true;
};

const submitReview = async () => {
  const token = localStorage.getItem('access_token');
  submittingReview.value = true;
  try {
    await axios.post(`${BASE_URL}/api/reviews`, {
      product_id: activeProductId.value,
      rating: reviewForm.value.rating,
      comment: reviewForm.value.comment
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });
    alert('Terima kasih! Review berhasil disimpan.');
    isReviewModalOpen.value = false;
    fetchProducts(); 
    if (activeProduct.value && activeProduct.value.id === activeProductId.value) {
      openDetailModal(activeProduct.value);
    }
  } catch (error) {
    alert('Gagal mengirim review.');
  } finally {
    submittingReview.value = false;
  }
};

const formatPrice = (price: any) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(price) || 0);
};
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-black text-slate-800">Product Portal</h1>
      <p class="text-sm font-bold text-slate-500 mt-1">Eksplorasi produk, pesan layanan, dan berikan ulasan Anda.</p>
    </div>

    <div v-if="loading" class="text-center py-20 text-slate-400 font-bold">Memuat katalog...</div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="product in products" :key="product.id" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm flex flex-col transition-shadow hover:shadow-md">
        
        <div class="h-40 bg-slate-50 relative overflow-hidden flex items-center justify-center border-b border-slate-100">
          <img v-if="product.image" :src="`${BASE_URL}/storage/${product.image}`" class="w-full h-full object-cover" />
          <div v-else class="text-blue-300 font-black text-xl uppercase tracking-widest">{{ product.category }}</div>
        </div>
        
        <div class="p-5 flex flex-col flex-1">
          <div class="flex justify-between items-start mb-2">
            <h3 class="font-black text-slate-800 text-lg leading-tight">{{ product.name }}</h3>
            <span class="text-xs font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded">{{ product.category }}</span>
          </div>
          
          <p class="text-xs text-slate-500 line-clamp-2 mb-4 flex-1">{{ product.description }}</p>
          <p class="font-black text-indigo-600 text-lg mb-4">{{ formatPrice(product.price) }}</p>
          
          <div class="grid grid-cols-2 gap-2">
            
            <button v-if="(product.product_type === 'software' && !userTierSlug.includes('gold')) || (product.product_type === 'digital' && isDownloadLimitReached())" disabled class="bg-slate-100 text-slate-400 border border-slate-200 py-2 rounded-lg text-xs font-black cursor-not-allowed shadow-sm flex items-center justify-center gap-1.5 transition-colors">
              🔒 {{ product.product_type === 'digital' ? 'Limit Habis' : 'Terkunci' }}
            </button>
            
            <button v-else @click="handleOrderClick(product)" class="bg-[#51C4ED] text-white py-2 rounded-lg text-xs font-black hover:bg-[#42B8E6] transition-colors shadow-sm">
              {{ product.product_type === 'digital' ? 'Download Digital' : 'Pesan Sekarang' }}
            </button>
            
            <button @click="openReviewModal(product)" :class="[
              'py-2 rounded-lg text-xs font-black transition-colors flex items-center justify-center gap-1 shadow-sm border',
              getUserReview(product) ? 'bg-yellow-50 border-yellow-200 text-yellow-700 hover:bg-yellow-100' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'
            ]">
              ⭐ {{ getUserReview(product) ? 'Ganti Review' : 'Beri Review' }}
            </button>
          </div>
          <button @click="openDetailModal(product)" class="mt-3 text-center w-full text-xs font-bold text-slate-400 hover:text-[#42B8E6] transition-colors">Lihat Detail Lengkap & Ulasan</button>
        </div>
      </div>
    </div>

    <div v-if="isReviewModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex justify-center items-center p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <h2 class="font-black text-slate-800">Tulis Ulasan Anda</h2>
          <button @click="isReviewModalOpen = false" class="text-slate-400 hover:text-red-500 text-xl font-black focus:outline-none">&times;</button>
        </div>
        
        <form @submit.prevent="submitReview" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 text-center">Bagaimana pengalaman Anda?</label>
            <div class="flex justify-center items-center gap-2">
              <button 
                type="button" v-for="star in 5" :key="star" 
                @click="reviewForm.rating = star" @mouseover="hoverRating = star" @mouseleave="hoverRating = 0"
                :class="[(hoverRating || reviewForm.rating) >= star ? 'text-yellow-400 scale-125' : 'text-slate-200 hover:text-yellow-300', 'text-4xl transition-all outline-none focus:outline-none']"
              >★</button>
            </div>
          </div>
          
          <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Komentar Terbuka</label>
            <textarea v-model="reviewForm.comment" required rows="4" placeholder="Fiturnya sangat lengkap dan mudah digunakan..." class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#42B8E6]"></textarea>
          </div>
          
          <button type="submit" :disabled="submittingReview" class="w-full bg-[#51C4ED] text-white py-3 rounded-lg text-sm font-black hover:bg-[#42B8E6] transition-colors shadow-sm disabled:opacity-50 flex items-center justify-center gap-2">
            {{ submittingReview ? 'Menyimpan Ulasan...' : 'Simpan Ulasan' }}
          </button>
        </form>
      </div>
    </div>

    <div v-if="isDetailModalOpen && activeProduct" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 flex justify-center items-center p-4 md:p-8">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden relative">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
          <div class="flex items-center gap-3">
            <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded text-[10px] font-black uppercase">{{ activeProduct.category }}</span>
            <h2 class="text-xl font-black text-slate-800">{{ activeProduct.name }}</h2>
          </div>
          <button @click="isDetailModalOpen = false" class="text-slate-400 hover:text-red-500 text-3xl font-black focus:outline-none leading-none">&times;</button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 bg-slate-50/50">
          <div class="flex flex-col md:flex-row gap-6 mb-8">
            <div class="w-full md:w-1/2 aspect-video bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-sm relative">
              <img v-if="activeProduct.image" :src="`${BASE_URL}/storage/${activeProduct.image}`" class="w-full h-full object-cover absolute inset-0" />
              <div v-else class="w-full h-full flex items-center justify-center font-black text-slate-300 text-2xl uppercase tracking-widest">{{ activeProduct.category }}</div>
            </div>
            <div class="w-full md:w-1/2 flex flex-col justify-center">
              <p class="text-sm text-slate-500 leading-relaxed mb-4">{{ activeProduct.description }}</p>
              <h3 class="text-3xl font-black text-indigo-600 mb-6">{{ formatPrice(activeProduct.price) }}</h3>
              
              <div class="flex gap-3">
                
                <button v-if="(activeProduct.product_type === 'software' && !userTierSlug.includes('gold')) || (activeProduct.product_type === 'digital' && isDownloadLimitReached())" disabled class="flex-1 bg-slate-100 text-slate-400 border border-slate-200 py-3.5 rounded-xl text-sm font-black cursor-not-allowed shadow-sm flex items-center justify-center gap-2">
                  🔒 {{ activeProduct.product_type === 'digital' ? 'Limit Download Habis' : 'Eksklusif Gold Member' }}
                </button>

                <button v-else @click="handleOrderClick(activeProduct)" class="flex-1 bg-[#51C4ED] text-white py-3.5 rounded-xl text-sm font-black hover:bg-[#42B8E6] transition-colors shadow-sm">
                  {{ activeProduct.product_type === 'digital' ? 'Download Sistem Ini' : 'Pesan Sistem Ini' }}
                </button>
                
                <button @click="openReviewModal(activeProduct)" class="flex-none px-5 bg-white border border-slate-200 text-slate-700 py-3.5 rounded-xl text-sm font-black hover:bg-slate-50 transition-colors shadow-sm">
                  ⭐
                </button>
              </div>  
            </div>
          </div>

          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-6">
            <h4 class="font-black text-slate-800 text-lg mb-3">Deskripsi Lengkap</h4>
            <div class="text-[13px] text-slate-600 leading-relaxed whitespace-pre-line">{{ activeProduct.overview || '-' }}</div>
          </div>

          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-6">
            <h4 class="font-black text-slate-800 text-lg mb-4">Fitur Utama</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-for="feat in activeProduct.features" :key="feat.id" class="flex gap-3">
                <div class="w-8 h-8 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center font-black flex-none">⚡</div>
                <div>
                  <h5 class="font-black text-slate-800 text-sm">{{ feat.title }}</h5>
                  <p class="text-[12px] text-slate-500 leading-snug">{{ feat.description }}</p>
                </div>
              </div>
            </div>
            <div v-if="!activeProduct.features?.length" class="text-xs font-bold text-slate-400">Belum ada fitur terdaftar.</div>
          </div>

          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h4 class="font-black text-slate-800 text-lg mb-4">Ulasan Pelanggan</h4>
            <div v-if="activeProduct.reviews && activeProduct.reviews.length > 0" class="space-y-4">
              <div v-for="review in activeProduct.reviews" :key="review.id" class="border border-slate-100 p-4 rounded-xl bg-slate-50/80">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#42B8E6] text-white flex items-center justify-center font-black text-xs uppercase">{{ review.user?.name?.charAt(0) || 'U' }}</div>
                    <div>
                      <h5 class="text-xs font-black text-slate-800">{{ review.user?.name || 'Customer' }}</h5>
                      <div class="text-[10px] text-yellow-400">{{ '⭐'.repeat(review.rating) }}</div>
                    </div>
                  </div>
                  <span v-if="review.user?.name === currentUserName" class="text-[9px] font-black bg-blue-100 text-blue-600 px-2 py-0.5 rounded uppercase tracking-wider">Review Anda</span>
                </div>
                <p class="text-[13px] text-slate-600 leading-snug ml-10">{{ review.comment }}</p>
              </div>
            </div>
            <div v-else class="text-center py-6">
              <p class="text-sm font-bold text-slate-400 mb-2">Belum ada ulasan untuk sistem ini.</p>
              <button @click="openReviewModal(activeProduct)" class="text-xs font-bold text-blue-500 hover:underline">Jadilah yang pertama mengulas!</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isSoftwareModalOpen" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4">
      <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl">
        <h2 class="font-black text-lg text-slate-800 mb-4">Pembayaran Software</h2>
        <p class="text-xs text-slate-500 mb-4 leading-relaxed">Sistem akan mengecek validitas pembayaran ini sebelum memberikan akses software sepenuhnya ke akun Anda.</p>
        
        <form @submit.prevent="submitSoftwareOrder" class="space-y-4">
          <div>
            <label class="text-xs font-bold block mb-1 text-slate-700">Metode Pembayaran</label>
            <select v-model="softwareForm.payment_method" required class="w-full border border-slate-200 bg-slate-50 rounded-lg p-2.5 text-sm font-bold outline-none focus:border-blue-500">
              <option value="BCA">BCA - 123456789 a.n PT Kerjapro</option>
              <option value="MANDIRI">MANDIRI - 987654321 a.n PT Kerjapro</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold block mb-1 text-slate-700">Upload Bukti Transfer</label>
            <input type="file" required accept="image/*" @change="(e: any) => softwareForm.proof = e.target.files[0]" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-lg p-1 bg-white">
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="isSoftwareModalOpen = false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg font-bold text-sm hover:bg-slate-200 transition">Batal</button>
            <button type="submit" :disabled="submittingOrder" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-sm flex-1 hover:bg-blue-700 transition disabled:opacity-50">
              {{ submittingOrder ? 'Memproses...' : 'Kirim Order' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="isPhysicalModalOpen" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4">
      <div class="bg-white p-6 rounded-2xl w-full max-w-lg shadow-2xl overflow-y-auto max-h-[90vh]">
        <h2 class="font-black text-lg text-slate-800 mb-4">Pengiriman Produk Fisik</h2>
        <form @submit.prevent="submitPhysicalOrder" class="space-y-4">
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-bold block mb-1 text-slate-700">Provinsi Tujuan</label>
              <select v-model="physicalForm.province_id" @change="fetchCities" required class="w-full border border-slate-200 bg-slate-50 rounded-lg p-2.5 text-sm outline-none focus:border-blue-500">
                <option value="" disabled>Pilih Provinsi...</option>
                <option v-for="prov in provinces" :key="prov.id || prov.province_id" :value="prov.id || prov.province_id">
                  {{ prov.name || prov.province }}
                </option>
              </select>
            </div>
            <div>
              <label class="text-xs font-bold block mb-1 text-slate-700">Kota/Kabupaten Tujuan</label>
              <select v-model="physicalForm.city_id" @change="checkOngkir" :disabled="!physicalForm.province_id" required class="w-full border border-slate-200 bg-slate-50 rounded-lg p-2.5 text-sm outline-none focus:border-blue-500 disabled:opacity-50">
                <option value="" disabled>Pilih Kota...</option>
                <option v-for="city in cities" :key="city.id || city.city_id" :value="city.id || city.city_id">
                  {{ city.type }} {{ city.name || city.city_name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="text-xs font-bold block mb-1 text-slate-700">Alamat Lengkap (Jalan, RT/RW, Kec)</label>
            <textarea v-model="physicalForm.detail_address" required rows="2" placeholder="Detail alamat pengiriman..." class="w-full border border-slate-200 rounded-lg p-3 text-sm outline-none focus:border-blue-500"></textarea>
          </div>

          <div>
            <label class="text-xs font-bold block mb-1 text-slate-700">Pilih Kurir Ekspedisi</label>
            <select v-model="physicalForm.courier" @change="handleCourierChange" required class="w-full border border-slate-200 bg-slate-50 rounded-lg p-2.5 text-sm font-bold outline-none focus:border-blue-500">
              <option value="" disabled>Pilih Kurir...</option>
              <option value="toko">🏪 Pengiriman Toko (Harga ditentukan Admin)</option>
              <option value="jne">JNE (Jalur Nugraha Ekakurir)</option>
              <option value="tiki">TIKI (Titipan Kilat)</option>
              <option value="pos">POS Indonesia</option>
            </select>
            <p v-if="physicalForm.courier === 'toko'" class="text-[10px] text-amber-600 bg-amber-50 p-2 mt-2 rounded border border-amber-100">
              Biaya pengiriman akan dicek secara manual oleh Admin. Anda akan diminta mentransfer setelah total harga muncul di menu Pesanan.
            </p>
          </div>

          <div v-if="checkingOngkir" class="text-center p-4 text-xs font-bold text-blue-500">Menghitung tarif ongkos kirim...</div>
          
          <div v-else-if="ongkirOptions.length > 0" class="border border-blue-200 bg-blue-50 p-3 rounded-lg space-y-2">
            <label class="text-xs font-bold text-blue-800 block mb-2">Pilih Layanan & Tarif Asli:</label>
            
            <div v-for="opt in ongkirOptions" :key="opt.service" class="flex items-center gap-3 bg-white p-2 border border-slate-200 rounded-lg hover:border-blue-400 cursor-pointer" 
                 @click="physicalForm.service = opt.service; physicalForm.cost = opt.cost[0] ? opt.cost[0].value : opt.cost;">
              <input type="radio" :value="opt.service" v-model="physicalForm.service" 
                     @change="physicalForm.cost = opt.cost[0] ? opt.cost[0].value : opt.cost" required class="w-4 h-4 text-blue-600">
              <div class="flex-1">
                <p class="text-sm font-black text-slate-800">{{ opt.service }} <span class="text-xs font-normal text-slate-500 ml-1">({{ opt.cost[0] ? opt.cost[0].etd : opt.etd }} hari)</span></p>
                <p class="text-[10px] text-slate-500">{{ opt.description || opt.name }}</p>
              </div>
              <span class="text-sm font-black text-indigo-600">Rp {{ (opt.cost[0] ? opt.cost[0].value : opt.cost).toLocaleString('id-ID') }}</span>
            </div>
          </div>
          
          <div v-else-if="physicalForm.city_id && physicalForm.courier !== 'toko' &&!checkingOngkir" class="text-center p-4 text-xs font-bold text-red-500">Layanan kurir ini tidak tersedia untuk kota tujuan Anda.</div>

          <div class="flex gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="isPhysicalModalOpen = false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg font-bold text-sm hover:bg-slate-200 transition">Batal</button>
            <button type="submit" :disabled="submittingOrder || (physicalForm.courier !== 'toko' && physicalForm.cost === 0)" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-sm flex-1 hover:bg-blue-700 transition disabled:opacity-50">
              {{ submittingOrder ? 'Memproses...' : 'Lanjutkan Pesanan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>