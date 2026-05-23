<script setup lang="ts">
const menus = [
  { name: 'Home', path: '/customer' },
  { name: 'Produk', path: '/customer/products' },
  { name: 'Order', path: '/customer/orders' },
  { name: 'Billing', path: '/customer/billing' },
  { name: 'Support', path: '/customer/support' },
];
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
      <div class="font-black text-xl text-indigo-600 italic tracking-wider">
        MY PORTAL
      </div>
      <div class="flex gap-6">
        <router-link 
          v-for="menu in menus" 
          :key="menu.name" 
          :to="menu.path"
          class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-wide"
          exact-active-class="text-indigo-600 border-b-2 border-indigo-600 pb-1"
        >
          {{ menu.name }}
        </router-link>
      </div>
      <div class="flex items-center gap-4">
        <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-indigo-100"></div>
        <span class="text-sm font-bold text-slate-700">Hi, Customer</span>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8">
      <router-view />
    </main>
  </div>
</template>