<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const BASE_URL = 'http://localhost:8000';
const products = ref<any[]>([]);
const loading = ref(true);

// Mengambil nama user yang sedang login dari memori
const currentUserName = ref(localStorage.getItem('user_name') || '');

// --- STATE MODAL REVIEW ---
const isReviewModalOpen = ref(false);
const activeProductId = ref<number | null>(null);
const reviewForm = ref({ rating: 5, comment: '' });
const hoverRating = ref(0);
const submittingReview = ref(false);

// --- STATE MODAL DETAIL PRODUK ---
const isDetailModalOpen = ref(false);
const activeProduct = ref<any>(null);

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

onMounted(fetchProducts);

// Mengecek apakah user saat ini sudah mereview produk tertentu
const getUserReview = (product: any) => {
  if (!product || !product.reviews) return null;
  return product.reviews.find((r: any) => r.user?.name === currentUserName.value);
};

const placeOrderDirect = async (productId: number) => {
  const token = localStorage.getItem('access_token');
  try {
    await axios.post(`${BASE_URL}/api/customer/orders`, { product_id: productId }, {
      headers: { Authorization: `Bearer ${token}` }
    });
    alert('Pesanan berhasil dibuat!');
    router.push('/dashboard/customer/orders');
  } catch (error) {
    alert('Gagal memproses pesanan.');
  }
};

// Membuka Modal Detail Produk
const openDetailModal = async (product: any) => {
  try {
    // Kita panggil ulang API detailnya agar mendapatkan fitur, FAQ, dan changelog
    const response = await axios.get(`${BASE_URL}/api/products/${product.slug || product.id}`);
    activeProduct.value = response.data;
    isDetailModalOpen.value = true;
  } catch (error) {
    alert('Gagal memuat detail produk.');
  }
};

// Membuka Modal Review
const openReviewModal = (product: any) => {
  activeProductId.value = product.id;
  
  // Cek jika sudah ada review, isi otomatis formnya!
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
    
    // Refresh katalog dan detail modal (jika terbuka)
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
    <!-- HEADER -->
    <div class="mb-6">
      <h1 class="text-2xl font-black text-slate-800">Product Portal</h1>
      <p class="text-sm font-bold text-slate-500 mt-1">Eksplorasi produk, pesan layanan, dan berikan ulasan Anda.</p>
    </div>

    <div v-if="loading" class="text-center py-20 text-slate-400 font-bold">Memuat katalog...</div>
    
    <!-- GRID PRODUK -->
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
            <button @click="placeOrderDirect(product.id)" class="bg-[#51C4ED] text-white py-2 rounded-lg text-xs font-black hover:bg-[#42B8E6] transition-colors shadow-sm">
              Pesan Sekarang
            </button>
            
            <!-- Logika Perubahan Tombol Review -->
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

    <!-- MODAL REVIEW -->
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

    <!-- MODAL DETAIL PRODUK EKSKLUSIF (POPUP) -->
    <div v-if="isDetailModalOpen && activeProduct" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 flex justify-center items-center p-4 md:p-8">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden relative">
        
        <!-- Header Modal Detail -->
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
          <div class="flex items-center gap-3">
            <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded text-[10px] font-black uppercase">{{ activeProduct.category }}</span>
            <h2 class="text-xl font-black text-slate-800">{{ activeProduct.name }}</h2>
          </div>
          <button @click="isDetailModalOpen = false" class="text-slate-400 hover:text-red-500 text-3xl font-black focus:outline-none leading-none">&times;</button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 bg-slate-50/50">
          
          <!-- Banner & Info Harga -->
          <div class="flex flex-col md:flex-row gap-6 mb-8">
            <div class="w-full md:w-1/2 aspect-video bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-sm relative">
              <img v-if="activeProduct.image" :src="`${BASE_URL}/storage/${activeProduct.image}`" class="w-full h-full object-cover absolute inset-0" />
              <div v-else class="w-full h-full flex items-center justify-center font-black text-slate-300 text-2xl uppercase tracking-widest">{{ activeProduct.category }}</div>
            </div>
            <div class="w-full md:w-1/2 flex flex-col justify-center">
              <p class="text-sm text-slate-500 leading-relaxed mb-4">{{ activeProduct.description }}</p>
              <h3 class="text-3xl font-black text-indigo-600 mb-6">{{ formatPrice(activeProduct.price) }}</h3>
              
              <div class="flex gap-3">
                <button @click="placeOrderDirect(activeProduct.id)" class="flex-1 bg-[#51C4ED] text-white py-3.5 rounded-xl text-sm font-black hover:bg-[#42B8E6] transition-colors shadow-sm">
                  Pesan Sistem Ini
                </button>
                <button @click="openReviewModal(activeProduct)" class="flex-none px-5 bg-white border border-slate-200 text-slate-700 py-3.5 rounded-xl text-sm font-black hover:bg-slate-50 transition-colors shadow-sm">
                  ⭐
                </button>
              </div>
            </div>
          </div>

          <!-- Deskripsi Lengkap -->
          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-6">
            <h4 class="font-black text-slate-800 text-lg mb-3">Deskripsi Lengkap</h4>
            <div class="text-[13px] text-slate-600 leading-relaxed whitespace-pre-line">{{ activeProduct.overview || '-' }}</div>
          </div>

          <!-- Fitur -->
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

          <!-- Review List Modal -->
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
                  <!-- Label jika itu adalah review milik dia sendiri -->
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
  </div>
</template>