<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';

const router = useRouter();

// State dipersingkat
const name = ref('');
const phoneCode = ref('+62');
const phoneNumber = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');

const errorMessage = ref('');
const successMessage = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const handleRegister = async () => {
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Password dan Konfirmasi Password tidak cocok!';
    return;
  }

  try {
    const response = await api.post('/register', {
      name: name.value,
      phone: `${phoneCode.value}${phoneNumber.value}`,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value 
    });
    
    successMessage.value = 'Registrasi berhasil! Mengarahkan ke halaman login...';
    errorMessage.value = '';
    
    setTimeout(() => {
      router.push('/customer/login');
    }, 2000);
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Gagal mendaftar. Pastikan data valid.';
    successMessage.value = '';
  }
};

// Fungsi Redirect ke Google OAuth Laravel
const loginWithGoogle = () => {
  window.location.href = 'http://localhost:8000/api/auth/google';
};
</script>

<template>
  <div class="px-4 md:px-8 pb-12">
    <div class="w-full max-w-5xl mx-auto mt-6 rounded-3xl min-h-[700px] flex items-center justify-center p-6 py-10 relative overflow-hidden shadow-inner bg-cover bg-center" style="background-image: url('/bg_log.png');">
      <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl p-8 relative z-10 my-6">
        
        <div class="flex justify-center mb-8">
          <img src="/reg.png" alt="Register Logo" class="w-28 h-28 object-contain">
        </div>

        <p v-if="errorMessage" class="text-red-500 text-xs mb-4 text-center bg-red-50 p-2 rounded border border-red-100">{{ errorMessage }}</p>
        <p v-if="successMessage" class="text-green-600 text-xs mb-4 text-center bg-green-50 p-2 rounded border border-green-100">{{ successMessage }}</p>

        <button @click="loginWithGoogle" type="button" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 hover:bg-gray-50 hover:shadow-md text-gray-600 font-semibold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 text-sm mb-6 group">
          <svg class="w-5 h-5 transition-transform group-hover:scale-110" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          <span>Sign up with Google</span>
        </button>

        <div class="flex items-center mb-6">
          <div class="flex-1 h-px bg-slate-200"></div>
          <span class="px-3 text-xs text-slate-400 font-bold">ATAU DAFTAR MANUAL</span>
          <div class="flex-1 h-px bg-slate-200"></div>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Name*</label>
            <input v-model="name" type="text" placeholder="Enter your name" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] transition" required />
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number*</label>
            <div class="flex gap-2">
              <select v-model="phoneCode" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] w-24">
                <option value="+62">+62</option>
              </select>
              <input v-model="phoneNumber" type="tel" placeholder="Enter phone number" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] transition" required />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Email*</label>
            <input v-model="email" type="email" placeholder="Enter your email" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] transition" required />
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Password*</label>
            <div class="relative">
              <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Min. 8 characters" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] transition pr-10" required minlength="8" />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <svg v-if="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Confirm Password*</label>
            <div class="relative">
              <input v-model="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'" placeholder="Confirm your password" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] transition pr-10" required minlength="8" />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <svg v-if="!showConfirmPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
              </button>
            </div>
          </div>

          <button type="submit" class="w-[75%] mx-auto block bg-[#66C2EC] hover:bg-[#4BB4E6] text-white font-black py-2.5 rounded-xl shadow-[0_6px_12px_rgba(0,0,0,0.15)] transition-all transform hover:-translate-y-0.5 text-sm tracking-widest mt-4">
              Sign up
          </button>
        </form>
        
        <p class="text-center text-xs text-gray-500 mt-6">
          Joined us Before? <router-link to="/customer/login" class="text-[#FFD700] font-bold hover:underline">Sign in</router-link>
        </p>

      </div>
    </div>
  </div>
</template>