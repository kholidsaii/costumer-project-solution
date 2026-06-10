<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const product = ref<any>(null);
const activeTab = ref('Overview');
const loading = ref(true);

const BASE_URL = 'http://localhost:8000';
const tabs = ['Overview', 'Features', 'Screenshots', 'Reviews', 'FAQ', 'Changelog'];

const fetchProductData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`${BASE_URL}/api/products/${route.params.slug}`);
    product.value = response.data;
  } catch (error) {
    console.error('Error fetching product detail:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProductData);

const handlePlaceOrder = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    localStorage.setItem('intended_order_product_id', product.value.id);
    router.push('/customer/login');
  } else {
    try {
      await axios.post(`${BASE_URL}/api/customer/orders`, { product_id: product.value.id }, {
        headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
      });
      router.push('/dashboard/customer/orders');
    } catch (error) {
      alert('Gagal memproses pemesanan.');
    }
  }
};

const formatPrice = (price: any) => {
  const numericPrice = Number(price);
  if (isNaN(numericPrice) || !price) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(numericPrice);
};
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 md:px-0 py-8 flex flex-col md:flex-row gap-6">
    
    <aside class="w-full md:w-64 flex-none bg-[#42B8E6] text-white rounded-xl overflow-hidden shadow-sm h-fit pb-6 hidden lg:block">
      <div @click="router.push('/customer/products')" class="bg-white text-[#42B8E6] p-4 m-2 rounded-lg font-black flex items-center gap-2 shadow-sm cursor-pointer hover:bg-slate-50 transition">
        <span class="text-xl">🏠</span> ALL PRODUCTS
      </div>
      <div class="px-6 py-4 space-y-4 border-b border-white/20">
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Personal Package</button>
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Education Package</button>
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Business Package</button>
      </div>
    </aside>

    <main v-if="loading" class="flex-1 flex justify-center items-center py-20 text-slate-400 font-bold">
      Loading Detail...
    </main>

    <main v-else-if="product" class="flex-1 min-w-0">
      
      <div class="text-[13px] font-bold text-slate-400 mb-6 flex items-center gap-2">
        <router-link to="/customer/products" class="hover:text-[#42B8E6]">Products</router-link> 
        <span>&gt;</span> 
        <span class="text-slate-600">{{ product.name }}</span>
      </div>

      <div class="flex flex-col lg:flex-row gap-6 mb-8 items-start">
        <div class="w-full lg:w-72 h-48 bg-blue-50 rounded-xl border border-blue-100 flex items-center justify-center font-black text-blue-300 text-2xl flex-none overflow-hidden relative shadow-sm">
          <img v-if="product.image" :src="`${BASE_URL}/storage/${product.image}`" :alt="product.name" class="w-full h-full object-cover absolute inset-0" />
          <span v-else>{{ product.category }}</span>
        </div>
        
        <div class="flex-1">
          <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">{{ product.category }}</p>
          <h1 class="text-2xl font-black text-slate-800 mb-3">{{ product.name }}</h1>
          <p class="text-[13px] text-slate-500 leading-relaxed mb-4">{{ product.description }}</p>
          
          <div class="flex items-center gap-1 text-sm mb-4">
            <div class="flex text-lg">
              <span v-for="star in 5" :key="star" :class="star <= Math.round(product.reviews_avg_rating || 0) ? 'text-yellow-400' : 'text-slate-200'">
                ★
              </span>
            </div>
            <span class="text-slate-400 text-xs ml-2 font-bold">
              {{ product.reviews_avg_rating ? parseFloat(product.reviews_avg_rating).toFixed(1) : '0.0' }} ({{ product.reviews_count || 0 }} reviews)
            </span>
          </div>
        </div>

        <div class="w-full lg:w-64 border border-slate-200 rounded-xl p-5 shadow-sm bg-white flex-none">
          <h2 class="text-2xl font-black text-slate-800 mb-1">{{ formatPrice(product.price) }}</h2>
          <p class="text-[11px] text-slate-400 font-bold mb-6 italic">/ One Time Payment</p>
          <ul class="space-y-3 text-[13px] text-slate-700 font-bold mb-6">
            <li class="flex items-center gap-2">✔️ Full Access</li>
            <li class="flex items-center gap-2">✔️ Lifetime Updates</li>
            <li class="flex items-center gap-2">✔️ 24/7 Support</li>
          </ul>
          <button @click="handlePlaceOrder" class="w-full bg-[#51C4ED] text-white py-3 rounded-lg text-sm font-black hover:bg-[#42B8E6] transition-colors flex justify-center items-center gap-2">
            🛍️ Place Order
          </button>
        </div>
      </div>

      <div class="flex gap-6 border-b border-slate-200 mb-6 overflow-x-auto">
        <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
          :class="['pb-3 text-sm font-black transition-colors border-b-2 whitespace-nowrap', activeTab === tab ? 'border-[#42B8E6] text-slate-800' : 'border-transparent text-slate-400 hover:text-slate-600']">
          {{ tab }}
        </button>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1">
          <div v-if="activeTab === 'Overview'">
            <h3 class="text-lg font-black text-slate-800 mb-3">Description</h3>
            <div class="text-[13px] text-slate-600 leading-relaxed mb-8 whitespace-pre-line">{{ product.overview || '-' }}</div>
            <h3 class="text-lg font-black text-slate-800 mb-4">Key Features</h3>
            <div v-if="product.features && product.features.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-for="feat in product.features" :key="feat.id" class="flex gap-4">
                <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center font-black flex-none">⚡</div>
                <div>
                  <h4 class="font-black text-slate-800 text-sm mb-1">{{ feat.title }}</h4>
                  <p class="text-[12px] text-slate-500 leading-relaxed">{{ feat.description }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-xs text-slate-400 italic">No features defined.</div>
          </div>
          
          <div v-else-if="activeTab === 'Features'">
            <h3 class="text-lg font-black text-slate-800 mb-4">All Features</h3>
            <div v-if="product.features && product.features.length" class="space-y-4">
              <div v-for="feat in product.features" :key="feat.id" class="flex gap-4 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-lg flex items-center justify-center font-black flex-none">✔️</div>
                <div>
                  <h4 class="font-black text-slate-800 text-sm mb-1">{{ feat.title }}</h4>
                  <p class="text-[13px] text-slate-600 leading-relaxed">{{ feat.description }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-xs text-slate-400 italic">No features defined.</div>
          </div>

          <div v-else-if="activeTab === 'Screenshots'">
            <h3 class="text-lg font-black text-slate-800 mb-4">Product Screenshots</h3>
            <div v-if="product.screenshots && product.screenshots.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <img v-for="shot in product.screenshots" :key="shot.id" :src="`${BASE_URL}/storage/${shot.image_path}`" class="w-full h-auto rounded-xl border border-slate-200 shadow-sm hover:scale-[1.02] transition-transform cursor-pointer" />
            </div>
            <div v-else class="text-xs text-slate-400 italic">No screenshots available.</div>
          </div>

          <div v-else-if="activeTab === 'Reviews'">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-black text-slate-800">Customer Reviews</h3>
            </div>
            <div v-if="product.reviews && product.reviews.length > 0" class="space-y-4 mb-8">
              <div v-for="review in product.reviews" :key="review.id" class="border border-slate-100 p-4 rounded-xl bg-slate-50">
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-8 h-8 rounded-full bg-[#42B8E6] text-white flex items-center justify-center font-black text-xs uppercase">{{ review.user?.name?.charAt(0) || 'U' }}</div>
                  <div>
                    <h5 class="text-xs font-black text-slate-800">{{ review.user?.name || 'Customer' }}</h5>
                    <div class="text-[12px] flex">
                      <span v-for="star in 5" :key="star" :class="star <= review.rating ? 'text-yellow-400' : 'text-slate-200'">★</span>
                    </div>
                  </div>
                </div>
                <p class="text-[13px] text-slate-600">{{ review.comment }}</p>
              </div>
            </div>
            <div v-else class="text-sm text-slate-400 font-bold py-6 text-center">Belum ada review untuk produk ini.</div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-center shadow-sm">
              <h4 class="font-black text-sm text-blue-800 mb-2">Sudah Membeli Produk Ini?</h4>
              <p class="text-xs text-blue-600 mb-4">Bagikan pengalaman Anda! Masuk ke Portal Pelanggan untuk memberikan rating dan ulasan.</p>
              <button @click="router.push('/dashboard/customer/products')" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-xs font-black hover:bg-blue-700 transition-colors">
                Buka Portal Customer
              </button>
            </div>
          </div>

          <div v-else-if="activeTab === 'FAQ'">
            <h3 class="text-lg font-black text-slate-800 mb-4">Frequently Asked Questions</h3>
            <div v-if="product.faqs && product.faqs.length" class="space-y-3">
              <div v-for="faq in product.faqs" :key="faq.id" class="border border-slate-100 p-4 rounded-xl bg-slate-50">
                <h4 class="font-black text-slate-800 text-[13px] mb-2">Q: {{ faq.question }}</h4>
                <p class="text-[13px] text-slate-600 leading-relaxed">A: {{ faq.answer }}</p>
              </div>
            </div>
            <div v-else class="text-sm text-slate-400 font-bold py-6 text-center">Belum ada FAQ untuk produk ini.</div>
          </div>

          <div v-else-if="activeTab === 'Changelog'">
            <h3 class="text-lg font-black text-slate-800 mb-4">Riwayat Pembaruan</h3>
            <div v-if="product.changelogs && product.changelogs.length" class="space-y-4">
              <div v-for="log in product.changelogs" :key="log.id" class="border border-slate-100 p-4 rounded-xl bg-slate-50 relative pl-10 overflow-hidden">
                <div class="absolute left-4 top-5 w-2 h-2 rounded-full bg-[#42B8E6]"></div>
                <div class="absolute left-[19px] top-7 bottom-0 w-px bg-slate-200"></div>
                
                <div class="flex justify-between items-center mb-2">
                  <h4 class="font-black text-slate-800 text-[14px]">Versi {{ log.version }}</h4>
                  <span class="text-[11px] text-slate-400 font-bold bg-white px-2 py-1 rounded border border-slate-100">{{ log.release_date }}</span>
                </div>
                <p class="text-[13px] text-slate-600 whitespace-pre-line leading-relaxed">{{ log.changes }}</p>
              </div>
            </div>
            <div v-else class="text-sm text-slate-400 font-bold py-6 text-center">Belum ada riwayat update.</div>
          </div>

        </div>

        <div class="w-full lg:w-64 flex-none space-y-6">
          <div class="border border-slate-200 rounded-xl p-5 bg-white">
            <ul class="space-y-4 text-[12px]">
              <li class="flex justify-between border-b border-slate-100 pb-2">
                <span class="font-black text-slate-800">Kategori</span> 
                <span class="text-slate-500 font-bold">{{ product.category }}</span>
              </li>
              
              <li class="flex justify-between border-b border-slate-100 pb-2">
                <span class="font-black text-slate-800">Lisensi Akses</span> 
                <span class="bg-blue-50 text-blue-600 px-2 rounded font-black uppercase text-[10px]">{{ product.access_tier || 'ALL' }}</span>
              </li>
              
              <li v-if="product.product_type === 'physical'" class="flex justify-between border-b border-slate-100 pb-2">
                <span class="font-black text-slate-800">Stok Tersedia</span> 
                <span class="text-slate-500 font-bold">{{ product.stock }} Unit</span>
              </li>

              <li class="flex justify-between border-b border-slate-100 pb-2">
                <span class="font-black text-slate-800">Developer</span> 
                <span class="text-slate-500 font-bold">KerjaPro Solutions</span>
              </li>
            </ul>
          </div>
        </div>

      </div>
    </main>
  </div>
</template>