<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';

const router = useRouter();
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

    if (response.data.user.role === 'admin') {
      router.push('/admin/dashboard');
    } else {
      router.push('/dashboard/customer');
    }
  } catch (error: any) {
    errorMessage.value = 'Email atau password salah!';
  }
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
          </div>

          <div class="flex items-center justify-between px-1">
            <label class="flex items-center text-white text-[11px] font-bold cursor-pointer">
              <input type="checkbox" class="mr-2 w-3.5 h-3.5 rounded-sm border-white bg-transparent text-[#66C2EC] focus:ring-0 focus:ring-offset-0" />
              Remember me
            </label>
            <a href="#" class="text-[#FFD700] text-[11px] font-black hover:underline tracking-wide">
              Forgot Password?
            </a>
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