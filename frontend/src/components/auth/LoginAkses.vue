<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';

const router = useRouter();
const email = ref('');
const password = ref('');
const errorMessage = ref('');

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
      router.push('/dashboard/customer'); // <--- Arahkan ke rute panel sidebar baru ini
    }
  } catch (error: any) {
    errorMessage.value = 'Email atau password salah!';
  }
};
</script>

<template>
  <div class="px-4 md:px-8 mt-8 md:mt-12 pb-12">
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-10">
      <h2 class="text-3xl font-black text-slate-800 text-center mb-2">Login</h2>
      <p class="text-center text-slate-500 mb-8 text-sm">Masuk ke dashboard pelanggan Anda</p>
      
      <p v-if="errorMessage" class="text-red-500 text-sm mb-4 text-center bg-red-50 p-2 rounded">{{ errorMessage }}</p>

      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-sm font-bold text-slate-600 mb-1">Email</label>
          <input v-model="email" type="email" placeholder="contoh@email.com" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 bg-slate-50 focus:bg-white transition" required />
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-600 mb-1">Password</label>
          <input v-model="password" type="password" placeholder="••••••••" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 bg-slate-50 focus:bg-white transition" required />
        </div>
        <button type="submit" class="w-full bg-[#B48440] text-white font-bold py-3 rounded-xl hover:bg-[#966d33] shadow-md transition mt-4">
          Login Sekarang
        </button>
      </form>
      
      <p class="text-center text-sm text-slate-500 mt-6">
        Belum punya akun? <router-link to="/customer/register" class="text-blue-500 font-bold hover:underline">Daftar di sini</router-link>
      </p>
    </div>
  </div>
</template>