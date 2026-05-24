<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';

const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const successMessage = ref('');

const handleRegister = async () => {
  try {
    const response = await api.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value
    });
    successMessage.value = 'Registrasi berhasil! Mengarahkan ke halaman login...';
    errorMessage.value = '';
    
    setTimeout(() => {
      router.push('/customer/login');
    }, 2000);
  } catch (error: any) {
    errorMessage.value = 'Gagal mendaftar. Pastikan email belum digunakan dan password minimal 8 karakter.';
    successMessage.value = '';
  }
};
</script>

<template>
  <div class="px-4 md:px-8 mt-8 md:mt-12 pb-12">
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-10">
      <h2 class="text-3xl font-black text-slate-800 text-center mb-2">Register</h2>
      <p class="text-center text-slate-500 mb-8 text-sm">Bergabung dengan Kerjapro Solutions</p>
      
      <p v-if="errorMessage" class="text-red-500 text-sm mb-4 text-center bg-red-50 p-2 rounded">{{ errorMessage }}</p>
      <p v-if="successMessage" class="text-green-600 text-sm mb-4 text-center bg-green-50 p-2 rounded">{{ successMessage }}</p>

      <form @submit.prevent="handleRegister" class="space-y-5">
        <div>
          <label class="block text-sm font-bold text-slate-600 mb-1">Nama Lengkap</label>
          <input v-model="name" type="text" placeholder="Masukkan nama Anda" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 bg-slate-50 focus:bg-white transition" required />
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-600 mb-1">Email</label>
          <input v-model="email" type="email" placeholder="contoh@email.com" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 bg-slate-50 focus:bg-white transition" required />
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-600 mb-1">Password</label>
          <input v-model="password" type="password" placeholder="Minimal 8 karakter" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 bg-slate-50 focus:bg-white transition" required minlength="8" />
        </div>
        <button type="submit" class="w-full bg-[#B48440] text-white font-bold py-3 rounded-xl hover:bg-[#966d33] shadow-md transition mt-4">
          Daftar Akun
        </button>
      </form>
      
      <p class="text-center text-sm text-slate-500 mt-6">
        Sudah punya akun? <router-link to="/customer/login" class="text-blue-500 font-bold hover:underline">Masuk di sini</router-link>
      </p>
    </div>
  </div>
</template>