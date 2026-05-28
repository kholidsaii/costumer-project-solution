<script setup lang="ts">
import { ref } from 'vue';
import api from '../../api/axios';

const email = ref('');
const message = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

const handleForgotPassword = async () => {
  isLoading.value = true;
  message.value = '';
  errorMessage.value = '';

  try {
    const response = await api.post('/forgot-password', {
      email: email.value
    });
    message.value = response.data.message;
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengirim email reset.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="px-4 md:px-8 pb-12">
    <div class="w-full max-w-5xl mx-auto mt-6 rounded-3xl min-h-[650px] flex items-center justify-center p-6 relative overflow-hidden shadow-inner bg-cover bg-center" style="background-image: url('/bg_log.png');">
      
      <div class="w-full max-w-xs relative z-10 my-8">
        
        <div class="flex justify-center mb-8">
          <img src="/forgot.png" alt="Logo" class="w-28 h-28 object-contain">
        </div>

        <p v-if="errorMessage" class="text-red-500 text-xs mb-4 text-center bg-white/90 p-2 rounded-lg font-bold shadow-md">{{ errorMessage }}</p>
        <p v-if="message" class="text-green-600 text-xs mb-4 text-center bg-white/90 p-2 rounded-lg font-bold shadow-md">{{ message }}</p>

        <form @submit.prevent="handleForgotPassword" class="space-y-4">
          <p class="text-white text-xs font-bold text-center mb-4">Masukkan email Anda untuk menerima link reset password.</p>
          
          <div>
            <input v-model="email" type="email" placeholder="Email Terdaftar" class="w-full px-4 py-3 bg-white border-none rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFD700] shadow-md" required :disabled="isLoading" />
          </div>

          <div class="pt-4">
            <button type="submit" :disabled="isLoading" class="w-[75%] mx-auto block bg-[#66C2EC] hover:bg-[#4BB4E6] text-white font-black py-2.5 rounded-xl shadow-[0_6px_12px_rgba(0,0,0,0.15)] transition-all transform hover:-translate-y-0.5 text-sm tracking-widest disabled:opacity-50 disabled:cursor-not-allowed">
              {{ isLoading ? 'Mengirim...' : 'Kirim Link' }}
            </button>
          </div>
        </form>
        
        <p class="text-center text-[11px] text-white mt-6 font-bold tracking-wide">
          Ingat password Anda? 
          <router-link to="/customer/login" class="text-[#FFD700] font-black hover:underline">
            Sign in
          </router-link>
        </p>

      </div>
    </div>
  </div>
</template>