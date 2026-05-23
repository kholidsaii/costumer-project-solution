<script setup lang="ts">
import { ref } from 'vue';
const activeTab = ref('overview');
const tabs = ['overview', 'order', 'billing', 'customer', 'maps'];
</script>

<template>
  <div class="p-8">
    <h2 class="text-2xl font-black uppercase mb-6 italic">Sales Management</h2>
    
    <div class="flex space-x-6 border-b border-slate-100 mb-8">
      <button v-for="tab in tabs" :key="tab" 
              @click="activeTab = tab"
              :class="['pb-4 uppercase text-[10px] font-black tracking-widest', 
              activeTab === tab ? 'text-[#4318FF] border-b-2 border-[#4318FF]' : 'text-slate-400']">
        {{ tab }}
      </button>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm">
      <component :is="'div'">Konten untuk {{ activeTab }}</component>
    </div>
  </div>
</template>