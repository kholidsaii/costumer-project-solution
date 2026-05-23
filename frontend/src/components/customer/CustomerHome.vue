<script setup lang="ts">
</script>

<template>
  <div class="px-8">
    <div class="mb-8">
      <h1 class="text-3xl font-black text-slate-800 uppercase italic">Selamat Datang!</h1>
      <p class="text-sm text-slate-500 font-bold mt-2">Pilih produk solusi kami atau pantau status pesanan Anda.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
        <div>
          <h3 class="text-lg font-black text-indigo-600 uppercase mb-2">Jelajahi Produk</h3>
          <p class="text-sm text-slate-500">Lihat katalog produk terbaru dan lakukan pemesanan dengan mudah.</p>
        </div>
        <router-link to="/customer/products" class="mt-6 bg-indigo-600 text-white text-center py-3 rounded-lg font-bold text-sm hover:bg-indigo-700 transition">
          Lihat Katalog
        </router-link>
      </div>

      <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
        <div>
          <h3 class="text-lg font-black text-emerald-600 uppercase mb-2">Pesanan Aktif</h3>
          <p class="text-sm text-slate-500">Anda memiliki 1 pesanan yang sedang diproses.</p>
        </div>
        <router-link to="/customer/orders" class="mt-6 border-2 border-emerald-600 text-emerald-600 text-center py-3 rounded-lg font-bold text-sm hover:bg-emerald-50 transition">
          Cek Status Pesanan
        </router-link>
      </div>
    </div>
  </div>
</template>