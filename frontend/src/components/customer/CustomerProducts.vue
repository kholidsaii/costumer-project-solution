<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const products = ref<any[]>([]);
const loading = ref(true);

// Tambahkan BASE URL Laravel
const BASE_URL = 'http://localhost:8000';

// State Filter
const selectedCategory = ref('All Categories');
const selectedPrice = ref('All Prices');
const selectedPopularity = ref('Newest');
const searchQuery = ref('');

// Opsi Kategori untuk Tab Atas
const topCategories = ['All', 'Software', 'Template', 'Integration', 'Service'];
const activeTopCategory = ref('All');

const fetchProducts = async () => {
  loading.value = true;
  try {
    let priceParam = '';
    if (selectedPrice.value === 'IDR 0 - IDR 100,000') priceParam = '0-100k';
    else if (selectedPrice.value === 'IDR 100,000 - IDR 500,000') priceParam = '100k-500k';
    else if (selectedPrice.value === 'Over IDR 500,000') priceParam = 'over-500k';

    const categoryParam = activeTopCategory.value !== 'All' ? activeTopCategory.value : (selectedCategory.value !== 'All Categories' ? selectedCategory.value : '');

    // PERBAIKAN: Tambahkan BASE_URL di sini
    const response = await axios.get(`${BASE_URL}/api/products`, {
      params: { category: categoryParam, price_range: priceParam, popularity: selectedPopularity.value }
    });
    
    products.value = Array.isArray(response.data) ? response.data : (response.data.data || []);
  } catch (error) {
    console.error('Error fetching products:', error);
    products.value = [];
  } finally {
    loading.value = false;
  }
};

watch([selectedCategory, selectedPrice, selectedPopularity, activeTopCategory], fetchProducts);
onMounted(fetchProducts);

const formatPrice = (price: any) => {
  const numericPrice = Number(price);
  if (isNaN(numericPrice) || !price) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(numericPrice);
};
</script>

<template>
  <!-- PERBAIKAN: Mengubah max-w-7xl menjadi max-w-5xl agar persis sejajar dengan Header Banner -->
  <div class="max-w-5xl mx-auto px-4 md:px-0 py-8 flex flex-col md:flex-row gap-6">
    
    <!-- SIDEBAR KIRI -->
    <aside class="w-full md:w-64 flex-none bg-[#42B8E6] text-white rounded-xl overflow-hidden shadow-sm h-fit pb-6">
      <div class="bg-white text-[#42B8E6] p-4 m-2 rounded-lg font-black flex items-center gap-2 shadow-sm">
        <span class="text-xl">🏠</span> ALL PRODUCTS
      </div>
      
      <!-- Package Links -->
      <div class="px-6 py-4 space-y-4 border-b border-white/20">
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Personal Package</button>
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Education Package</button>
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Business Package</button>
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Enterprise Package</button>
        <button class="flex items-center gap-3 w-full text-left font-bold text-sm hover:opacity-80"><span class="text-xl opacity-70">⊞</span> Creative Package</button>
      </div>

      <div class="px-6 mt-6">
        <h3 class="font-bold text-sm mb-4 opacity-90">Product Filter</h3>
        
        <!-- Categories -->
        <div class="mb-6">
          <h4 class="font-bold text-sm mb-2">Categories</h4>
          <div class="space-y-2">
            <label v-for="cat in ['All Categories', 'Software', 'Templates', 'Integrations', 'Services']" :key="cat" class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" :value="cat" v-model="selectedCategory" class="accent-white" /> <span class="opacity-90">{{ cat }}</span>
            </label>
          </div>
        </div>

        <!-- Price -->
        <div class="mb-6">
          <h4 class="font-bold text-sm mb-2">Price</h4>
          <div class="space-y-2">
            <label v-for="price in ['All Prices', 'IDR 0 - IDR 100,000', 'IDR 100,000 - IDR 500,000', 'Over IDR 500,000']" :key="price" class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" :value="price" v-model="selectedPrice" class="accent-white" /> <span class="opacity-90">{{ price }}</span>
            </label>
          </div>
        </div>

        <!-- Popularity -->
        <div>
          <h4 class="font-bold text-sm mb-2">Popularity</h4>
          <div class="space-y-2">
            <label v-for="pop in ['Best Seller', 'Top Rated', 'Newest']" :key="pop" class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" :value="pop" v-model="selectedPopularity" class="accent-white" /> <span class="opacity-90">{{ pop }}</span>
            </label>
          </div>
        </div>
      </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 min-w-0">
      
      <!-- Header Konten -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-2xl font-black text-slate-800">All Products</h1>
        <div class="flex gap-2 w-full md:w-auto">
          <div class="relative w-full md:w-48">
            <input type="text" v-model="searchQuery" placeholder="Search..." class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-[#42B8E6]" />
            <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
          </div>
          <select v-model="selectedPopularity" class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-600 bg-white focus:outline-none">
            <option value="Popularity">Popularity</option>
            <option value="Newest">Newest</option>
            <option value="Best Seller">Best Seller</option>
          </select>
        </div>
      </div>

      <!-- Tab Kategori & Layout Toggle -->
      <div class="flex gap-2 mb-6 overflow-x-auto pb-2 items-center bg-slate-100/50 p-1 rounded-xl">
        <div class="flex flex-1">
          <button v-for="cat in topCategories" :key="cat" @click="activeTopCategory = cat" :class="['px-5 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap', activeTopCategory === cat ? 'bg-[#51C4ED] text-white shadow-sm' : 'text-slate-500 hover:text-slate-700']">
            {{ cat }}
          </button>
        </div>
        <button class="p-2 border border-slate-200 rounded-lg bg-white text-slate-500 ml-2">𝌆</button>
      </div>

      <!-- Grid Produk -->
      <div v-if="loading" class="text-center py-20 text-slate-400 font-bold">Memuat data produk...</div>
      <div v-else-if="products.length === 0" class="text-center py-20 text-slate-400 font-bold">Produk tidak ditemukan. Pastikan Anda telah menambahkannya di Dashboard Admin.</div>
      
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div v-for="product in products" :key="product.id" class="bg-white border border-slate-200 rounded-xl overflow-hidden flex flex-col hover:shadow-lg transition-shadow group">
          <div class="h-32 bg-slate-50 relative p-4 flex items-center justify-center border-b border-slate-100">
            <button class="absolute top-2 right-2 text-slate-400 hover:text-yellow-400 transition-colors bg-white rounded-full p-1.5 shadow-sm border border-slate-100 z-10">⭐</button>
            
            <img v-if="product.image" :src="`http://localhost:8000/storage/${product.image}`" :alt="product.name" class="w-full h-full object-cover" />
            
            <div v-else class="w-full h-full bg-blue-100/50 rounded-lg flex items-center justify-center text-blue-300 font-black text-xl uppercase tracking-widest text-center px-2">
              {{ product.category || 'Product' }}
            </div>
          </div>
          
          <div class="p-4 flex flex-col flex-1 text-center">
            <!-- PERBAIKAN: Mencegah hilangnya text jika database kosong -->
            <h3 class="font-black text-slate-800 text-[14px] mb-2 group-hover:text-[#42B8E6] transition-colors line-clamp-1" :title="product.name">
              {{ product.name || 'Produk Tanpa Nama' }}
            </h3>
            <p class="text-[12px] text-slate-500 leading-relaxed line-clamp-2 mb-4 flex-1">
              {{ product.description || 'Deskripsi produk belum tersedia atau masih kosong.' }}
            </p>
            
            <p class="font-black text-slate-800 text-[14px] mb-4">{{ formatPrice(product.price) }}</p>
            
            <button @click="router.push(`/customer/products/${product.slug || product.id}`)" class="w-full bg-[#51C4ED] text-white py-2 rounded-lg text-xs font-bold hover:bg-[#42B8E6] transition-colors">
              View Details
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Trust Badges -->
      <div class="border-t border-slate-200 pt-6 mt-8 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
          <div><div class="text-2xl mb-1">✔️</div><h5 class="text-xs font-black text-slate-800 mb-1">High-Quality</h5></div>
          <div><div class="text-2xl mb-1">🔒</div><h5 class="text-xs font-black text-slate-800 mb-1">Secure</h5></div>
          <div><div class="text-2xl mb-1">🔄</div><h5 class="text-xs font-black text-slate-800 mb-1">Updates</h5></div>
          <div><div class="text-2xl mb-1">🎧</div><h5 class="text-xs font-black text-slate-800 mb-1">24/7 Support</h5></div>
        </div>
      </div>

    </main>
  </div>
</template>