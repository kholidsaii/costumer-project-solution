<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api/axios';

const router = useRouter();

const name = ref('');
const phoneCode = ref('+62');
const phoneNumber = ref('');
const email = ref('');
const dob = ref('');
const gender = ref('');
const country = ref('');
const address = ref('');
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
      date_of_birth: dob.value,
      gender: gender.value,
      country: country.value,
      address: address.value,
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
</script>

<template>
  <div class="px-4 md:px-8 pb-12">
    <div class="w-full max-w-7xl mx-auto mt-6 rounded-3xl min-h-[700px] flex items-center justify-center p-6 py-10 relative overflow-hidden shadow-inner bg-cover bg-center" style="background-image: url('/bg_log.png');">

      <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl p-8 relative z-10 my-6">
        
        <p v-if="errorMessage" class="text-red-500 text-xs mb-4 text-center bg-red-50 p-2 rounded border border-red-100">{{ errorMessage }}</p>
        <p v-if="successMessage" class="text-green-600 text-xs mb-4 text-center bg-green-50 p-2 rounded border border-green-100">{{ successMessage }}</p>

        <form @submit.prevent="handleRegister" class="space-y-4">
          
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Name*</label>
            <input v-model="name" type="text" placeholder="Enter your name" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition" required />
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number*</label>
            <div class="flex gap-2">
              <select v-model="phoneCode" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white w-24">
                <option value="+62">+62</option>
                <option value="+1">+1</option>
                <option value="+44">+44</option>
              </select>
              <input v-model="phoneNumber" type="tel" placeholder="Enter phone number" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition" required />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Email*</label>
            <input v-model="email" type="email" placeholder="Enter your email" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition" required />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Date of Birth*</label>
              <input v-model="dob" type="date" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition text-gray-500" required />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Gender*</label>
              <select v-model="gender" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition text-gray-500" required>
                <option value="" disabled>Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Country*</label>
            <select v-model="country" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition text-gray-500" required>
              <option value="" disabled>Select country</option>
              <option value="Indonesia">Indonesia</option>
              <option value="Malaysia">Malaysia</option>
              <option value="Singapore">Singapore</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Address*</label>
            <input v-model="address" type="text" placeholder="Enter your address" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition" required />
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Password*</label>
            <div class="relative">
              <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Min. 8 characters" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition pr-10" required minlength="8" />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Confirm Password*</label>
            <div class="relative">
              <input v-model="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'" placeholder="Confirm your password" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#1DA1F2] bg-white transition pr-10" required minlength="8" />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
            </div>
          </div>

          <p class="text-[10px] text-gray-400 text-center mt-2">
            By clicking "Sign up" You certify that you agree with our <a href="#" class="text-[#1DA1F2]">Privacy policy</a> and conditions.
          </p>

          <button type="submit" class="w-[75%] mx-auto block bg-[#66C2EC] hover:bg-[#4BB4E6] text-white font-black py-2.5 rounded-xl shadow-[0_6px_12px_rgba(0,0,0,0.15)] transition-all transform hover:-translate-y-0.5 text-sm tracking-widest">
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