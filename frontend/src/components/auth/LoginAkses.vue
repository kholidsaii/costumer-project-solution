<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../api/axios';

const router = useRouter();
const route = useRoute();
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const showPassword = ref(false);

const handleLogin = async () => {
  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    });
    
    localStorage.setItem('access_token', response.data.access_token);
    localStorage.setItem('user_role', response.data.user.role);
    localStorage.setItem('user_name', response.data.user.name);
    
    // --- TAMBAHAN: Simpan Master Tier dari Manual Login ---
    localStorage.setItem('user_tier_slug', response.data.user.tier_slug || 'free');
    localStorage.setItem('user_tier_name', response.data.user.tier_name || 'Free Member');

    if (response.data.user.role === 'admin') {
      router.push('/admin/dashboard');
    } else {
      router.push('/dashboard/customer');
    }
  } catch (error: any) {
    errorMessage.value = 'Email atau password salah!';
  }
};

onMounted(() => {
  const token = route.query.token as string;
  const role = route.query.role as string;
  const name = route.query.name as string;
  
  // --- TAMBAHAN: Tangkap tier dari Redirect Google ---
  const tierSlug = route.query.tier_slug as string; 
  const tierName = route.query.tier_name as string; 

  if (token) {
    localStorage.setItem('access_token', token);
    localStorage.setItem('user_role', role || 'customer');
    localStorage.setItem('user_name', name ? decodeURIComponent(name) : 'Pelanggan');
    
    // --- TAMBAHAN: Simpan tier ke storage ---
    localStorage.setItem('user_tier_slug', tierSlug || 'free'); 
    localStorage.setItem('user_tier_name', tierName ? decodeURIComponent(tierName) : 'Free Member'); 

    router.replace('/dashboard/customer');
  } else if (route.query.error) {
    errorMessage.value = 'Gagal terhubung dengan akun Google Anda.';
  }
});

const loginWithGoogle = () => {
  window.location.href = 'http://localhost:8000/api/auth/google';
};
</script>

<template>
  <div class="px-4 md:px-8 pb-12">
    <div class="w-full max-w-5xl mx-auto mt-6 rounded-3xl min-h-[650px] flex items-center justify-center p-6 relative overflow-hidden shadow-inner bg-cover bg-center" style="background-image: url('/bg_log.png');">
      
      <div class="w-full max-w-xs relative z-10 my-8">
        
        <div class="flex justify-center mb-8">
          <img src="/log.png" alt="Login Logo" class="w-28 h-28 object-contain">
        </div>

        <p v-if="errorMessage" class="text-red-500 text-xs mb-4 text-center bg-white/90 p-2 rounded-lg font-bold shadow-md">{{ errorMessage }}</p>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <input v-model="email" type="email" placeholder="Username or email" class="w-full px-4 py-3 bg-white border-none rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFD700] shadow-md" required />
          </div>

          <div class="relative">
            <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" class="w-full px-4 py-3 bg-white border-none rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFD700] shadow-md pr-10" required />
            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            </button>

            <button @click="loginWithGoogle" type="button" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 hover:bg-gray-50 hover:shadow-md text-gray-600 font-semibold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 text-sm mt-4 group">
              <svg class="w-5 h-5 transition-transform group-hover:scale-110" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              <span>Sign in with Google</span>
            </button>
          </div>

          <div class="flex items-center justify-between px-1">
            <label class="flex items-center text-white text-[11px] font-bold cursor-pointer">
              <input type="checkbox" class="mr-2 w-3.5 h-3.5 rounded-sm border-white bg-transparent text-[#66C2EC] focus:ring-0 focus:ring-offset-0" />
              Remember me
            </label>
            <router-link to="/customer/forgot-password" class="text-[#FFD700] text-[11px] font-black hover:underline tracking-wide">
              Forgot Password?
            </router-link>
          </div>

          <div class="pt-4">
            <button type="submit" class="w-[75%] mx-auto block bg-[#66C2EC] hover:bg-[#4BB4E6] text-white font-black py-2.5 rounded-xl shadow-[0_6px_12px_rgba(0,0,0,0.15)] transition-all transform hover:-translate-y-0.5 text-sm tracking-widest">
              Sign in
            </button>
          </div>
        </form>
        
        <p class="text-center text-[11px] text-white mt-6 font-bold tracking-wide">
          New to here! 
          <router-link to="/customer/register" class="text-[#FFD700] font-black hover:underline">
            Sign up
          </router-link>
        </p>

      </div>
    </div>
  </div>
</template>