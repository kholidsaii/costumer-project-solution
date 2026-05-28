<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../api/axios';

const router = useRouter();
const route = useRoute();

const email = ref('');
const token = ref('');
const password = ref('');
const confirmPassword = ref('');

const message = ref('');
const errorMessage = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Tangkap token dan email dari URL saat halaman dimuat
onMounted(() => {
  token.value = (route.query.token as string) || '';
  email.value = (route.query.email as string) || '';
  
  if (!token.value || !email.value) {
    errorMessage.value = 'Link reset password tidak valid atau tidak lengkap.';
  }
});

const handleReset = async () => {
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Password dan Konfirmasi Password tidak cocok!';
    return;
  }

  errorMessage.value = '';
  
  try {
    const response = await api.post('/reset-password', {
      email: email.value,
      token: token.value,
      password: password.value,
      password_confirmation: confirmPassword.value
    });
    
    message.value = response.data.message;
    
    // Pindah ke login setelah sukses
    setTimeout(() => {
      router.push('/customer/login');
    }, 2500);
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Gagal mereset password.';
  }
};
</script>

<template>
  <div class="px-4 md:px-8 pb-12">
    <div class="w-full max-w-5xl mx-auto mt-6 rounded-3xl min-h-[650px] flex items-center justify-center p-6 relative overflow-hidden shadow-inner bg-cover bg-center" style="background-image: url('/bg_log.png');">
      
      <div class="w-full max-w-xs relative z-10 my-8">
        
        <div class="flex justify-center mb-8">
          <img src="/reset.png" alt="Logo" class="w-28 h-28 object-contain">
        </div>

        <p v-if="errorMessage" class="text-red-500 text-xs mb-4 text-center bg-white/90 p-2 rounded-lg font-bold shadow-md">{{ errorMessage }}</p>
        <p v-if="message" class="text-green-600 text-xs mb-4 text-center bg-white/90 p-2 rounded-lg font-bold shadow-md">{{ message }}</p>

        <form @submit.prevent="handleReset" class="space-y-4">
          <p class="text-white text-xs font-bold text-center mb-4">Buat Password Baru Anda</p>
          
          <div class="relative">
            <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Password Baru" class="w-full px-4 py-3 bg-white border-none rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFD700] shadow-md pr-10" required minlength="8" />
            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
            </button>
          </div>

          <div class="relative">
            <input v-model="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'" placeholder="Konfirmasi Password" class="w-full px-4 py-3 bg-white border-none rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFD700] shadow-md pr-10" required minlength="8" />
            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
            </button>
          </div>

          <div class="pt-4">
            <button type="submit" class="w-[75%] mx-auto block bg-[#66C2EC] hover:bg-[#4BB4E6] text-white font-black py-2.5 rounded-xl shadow-[0_6px_12px_rgba(0,0,0,0.15)] transition-all transform hover:-translate-y-0.5 text-sm tracking-widest">
              Ubah Password
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>