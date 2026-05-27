<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// ==========================================
// 1. STATE MANAGEMENT
// ==========================================
const articles = ref<any[]>([]);
const isLoading = ref(true);
const isModalOpen = ref(false);
const isSubmitting = ref(false);

// State Profil User Aktif
const currentUser = ref({ 
  id: null as number | null, 
  name: 'Memuat...', 
  initial: '' 
});

// State Form Upload Media
const fileInput = ref<HTMLInputElement | null>(null);
const mediaFile = ref<File | null>(null);
const mediaPreview = ref<string | null>(null);

// State Form Postingan
const postForm = ref({
  title: '',
  category: 'DISKUSI UMUM',
  excerpt: '',
});

// API Base URL (Mendukung Environment Variables Vite)
const API_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

// ==========================================
// 2. HELPER FUNCTIONS & FORMATTER
// ==========================================

// Header Otentikasi Axios
const getHeaders = () => {
  const token = localStorage.getItem('access_token');
  return { Authorization: token ? `Bearer ${token}` : '' };
};

// Fungsi Waktu Ala LinkedIn (Cth: "2 jam yang lalu")
const timeAgo = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);
  
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + " tahun yang lalu";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + " bulan yang lalu";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + " hari yang lalu";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + " jam yang lalu";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + " menit yang lalu";
  return "Baru saja";
};

// ==========================================
// 3. API CALLS: USER & ARTICLES
// ==========================================

const fetchUser = async () => {
  try {
    const response = await axios.get(`${API_URL}/user`, { headers: getHeaders() });
    currentUser.value = {
      id: response.data.id,
      name: response.data.name,
      initial: response.data.name.charAt(0).toUpperCase()
    };
  } catch (error) {
    console.error("Autentikasi gagal atau sesi habis:", error);
  }
};

const fetchArticles = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(`${API_URL}/articles`, { headers: getHeaders() });
    
    // Injeksi state tambahan untuk UI setiap artikel agar independen
    articles.value = response.data.map((art: any) => ({
      ...art,
      showComments: false,
      isCommentsLoading: false, // Untuk animasi loading komentar
      commentList: [],
      newCommentText: ''
    }));
  } catch (error) {
    console.error("Gagal memuat feed artikel:", error);
  } finally {
    isLoading.value = false;
  }
};

// ==========================================
// 4. SISTEM INTERAKSI SOSIAL (LIKE & COMMENT)
// ==========================================

const toggleLike = async (article: any) => {
  try {
    // Optimistic UI Update (Berubah instan sebelum server membalas)
    article.is_liked_by_me = !article.is_liked_by_me;
    article.likes_count += article.is_liked_by_me ? 1 : -1;

    const response = await axios.post(`${API_URL}/articles/${article.id}/like`, {}, { headers: getHeaders() });
    
    // Sinkronisasi data final dari server untuk mencegah bug
    article.is_liked_by_me = response.data.is_liked;
    article.likes_count = response.data.likes_count;
  } catch (error) {
    console.error("Gagal menyukai postingan:", error);
    // Rollback UI jika request ke server gagal
    article.is_liked_by_me = !article.is_liked_by_me;
    article.likes_count += article.is_liked_by_me ? 1 : -1;
  }
};

const toggleComments = async (article: any) => {
  article.showComments = !article.showComments;
  
  // Hanya fetch dari API jika komentar dibuka dan list masih kosong
  if (article.showComments && article.commentList.length === 0 && article.comments_count > 0) {
    article.isCommentsLoading = true;
    try {
      const response = await axios.get(`${API_URL}/articles/${article.id}/comments`, { headers: getHeaders() });
      article.commentList = response.data;
    } catch (error) {
      console.error("Gagal memuat daftar komentar:", error);
    } finally {
      article.isCommentsLoading = false;
    }
  }
};

const submitComment = async (article: any) => {
  if (!article.newCommentText.trim()) return;

  try {
    const response = await axios.post(`${API_URL}/articles/${article.id}/comments`, 
      { content: article.newCommentText }, 
      { headers: getHeaders() }
    );
    
    // Masukkan komentar baru ke array dan update counter
    article.commentList.push(response.data);
    article.comments_count++;
    article.newCommentText = ''; 
  } catch (error) {
    console.error("Gagal mengirim komentar:", error);
    alert("Gagal mengirim komentar. Periksa koneksi Anda.");
  }
};

// ==========================================
// 5. SISTEM MANAJEMEN POSTINGAN (CRUD)
// ==========================================

const triggerFileInput = () => fileInput.value?.click();

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    
    // Validasi Ekstensi File Manual
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'video/mp4', 'video/quicktime'];
    if (!allowedTypes.includes(file.type)) {
      alert('Format file tidak didukung. Harap unggah Gambar atau Video (MP4).');
      return;
    }

    // Validasi Ukuran (Maksimal 20MB)
    if (file.size > 20 * 1024 * 1024) {
      alert('Ukuran file terlalu besar. Maksimal 20 MB.');
      if (fileInput.value) fileInput.value.value = '';
      return;
    }
    
    // Bersihkan preview lama dari memori browser
    if (mediaPreview.value) URL.revokeObjectURL(mediaPreview.value);
    
    mediaFile.value = file;
    mediaPreview.value = URL.createObjectURL(file);
  }
};

const removeSelectedMedia = () => {
  if (mediaPreview.value) URL.revokeObjectURL(mediaPreview.value); // Optimasi memori
  mediaFile.value = null;
  mediaPreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const closeModal = () => {
  isModalOpen.value = false;
  postForm.value = { title: '', category: 'DISKUSI UMUM', excerpt: '' };
  removeSelectedMedia();
};

const submitPost = async () => {
  if (!postForm.value.excerpt.trim()) return;
  isSubmitting.value = true;
  
  try {
    const formData = new FormData();
    formData.append('title', postForm.value.title);
    formData.append('category', postForm.value.category);
    formData.append('excerpt', postForm.value.excerpt);
    
    if (mediaFile.value) {
      formData.append('media', mediaFile.value);
    }
    
    const response = await axios.post(`${API_URL}/articles`, formData, {
      headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' }
    });

    const newArt = response.data.article;
    
    // Siapkan state reaktif UI untuk postingan yang baru ditambahkan
    newArt.likes_count = 0;
    newArt.comments_count = 0;
    newArt.is_liked_by_me = false;
    newArt.showComments = false;
    newArt.isCommentsLoading = false;
    newArt.commentList = [];
    newArt.newCommentText = '';

    // Push ke urutan paling atas di feed
    articles.value.unshift(newArt);
    closeModal();
  } catch (error) {
    console.error("Gagal membuat postingan:", error);
    alert("Terjadi kesalahan saat memposting. Pastikan sesi Anda aktif.");
  } finally {
    isSubmitting.value = false;
  }
};

const deletePost = async (articleId: number) => {
  if (!confirm("Tindakan ini tidak dapat dibatalkan. Yakin ingin menghapus postingan?")) return;

  try {
    await axios.delete(`${API_URL}/articles/${articleId}`, { headers: getHeaders() });
    
    // Hapus dari state array (Langsung hilang dari UI)
    articles.value = articles.value.filter(art => art.id !== articleId);
  } catch (error) {
    console.error("Gagal menghapus postingan:", error);
    alert("Akses ditolak. Anda hanya bisa menghapus postingan milik sendiri.");
  }
};

// Lifecycle: Dieksekusi saat komponen di-*mount* ke DOM
onMounted(() => {
  fetchUser();
  fetchArticles();
});
</script>

<template>
  <div class="bg-[#F3F2EF] min-h-screen pt-6 pb-12 font-sans relative"> 
    <div class="max-w-5xl mx-auto px-4 md:px-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-3 space-y-4">
          <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm text-center pb-4 sticky top-6">
            <div class="h-16 bg-gradient-to-r from-slate-200 to-slate-300"></div>
            
            <div class="w-16 h-16 bg-white rounded-full mx-auto -mt-8 border-2 border-white overflow-hidden shadow-sm flex items-center justify-center bg-blue-50 relative group cursor-pointer">
              <span v-if="currentUser.initial" class="font-bold text-3xl text-blue-600 group-hover:scale-110 transition-transform">
                {{ currentUser.initial }}
              </span>
              <svg v-else class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
            
            <div class="mt-2 px-4">
              <h2 class="font-bold text-slate-800 hover:underline cursor-pointer">{{ currentUser.name }}</h2>
              <p class="text-xs text-slate-500 mt-1">Kerjapro Member</p>
            </div>
            
            <div class="mt-4 border-t border-slate-100 text-left">
              <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer flex justify-between text-xs font-semibold text-slate-500 transition">
                <span>Koneksi Jaringan</span>
                <span class="text-blue-600">84</span>
              </div>
              <div class="px-4 py-3 border-t border-slate-100 hover:bg-slate-50 cursor-pointer flex justify-between text-xs font-semibold text-slate-500 transition">
                <span>Total Postingan</span>
                <span class="text-blue-600">{{ articles.filter(a => a.user_id === currentUser.id).length }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-6 space-y-4">
          
          <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4">
            <div class="flex gap-3 items-center mb-3">
              <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0 font-bold text-xl cursor-pointer hover:bg-blue-100 transition">
                {{ currentUser.initial || 'U' }}
              </div>
              <button @click="isModalOpen = true" class="flex-1 text-left bg-white border border-slate-400 rounded-full px-5 py-3 text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:border-slate-500 transition shadow-sm">
                Mulai berdiskusi, {{ currentUser.name.split(' ')[0] }}?
              </button>
            </div>
            
            <div class="flex justify-around pt-2">
              <button @click="isModalOpen = true" class="flex items-center gap-2 text-slate-600 hover:bg-slate-100 px-3 py-2 rounded-md font-semibold text-sm transition w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" /></svg>
                Media / File
              </button>
              <button @click="isModalOpen = true" class="flex items-center gap-2 text-slate-600 hover:bg-slate-100 px-3 py-2 rounded-md font-semibold text-sm transition w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>
                Tulis Artikel
              </button>
            </div>
          </div>

          <div class="flex items-center gap-2 text-xs text-slate-500 pb-1">
            <div class="flex-1 h-px bg-slate-300"></div>
            <span>Urutkan: <strong class="text-slate-800 cursor-pointer">Terbaru ▼</strong></span>
          </div>

          <div v-if="isLoading" class="space-y-4">
            <div v-for="n in 2" :key="n" class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 animate-pulse">
              <div class="flex gap-3 items-center mb-4">
                <div class="w-12 h-12 bg-slate-200 rounded-full"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                  <div class="h-2 bg-slate-200 rounded w-1/4"></div>
                </div>
              </div>
              <div class="space-y-2">
                <div class="h-3 bg-slate-200 rounded w-3/4"></div>
                <div class="h-3 bg-slate-200 rounded w-full"></div>
                <div class="h-3 bg-slate-200 rounded w-5/6"></div>
              </div>
              <div class="h-40 bg-slate-100 rounded-md mt-4"></div>
            </div>
          </div>

          <TransitionGroup v-else name="list" tag="div" class="space-y-4">
            
            <div v-if="articles.length === 0" key="empty" class="text-center py-12 bg-white rounded-lg border border-slate-200 shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H14" /></svg>
              <p class="text-slate-600 font-semibold text-lg">Belum ada aktivitas</p>
              <p class="text-slate-400 text-sm mt-1">Jadilah yang pertama memulai diskusi!</p>
            </div>

            <div v-for="article in articles" :key="article.id" class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden transition-all hover:shadow-md">
              
              <div class="p-4 flex gap-3 items-center relative group">
                <div class="w-12 h-12 bg-slate-100 text-slate-600 border border-slate-200 rounded-full flex items-center justify-center shrink-0 font-bold text-lg uppercase cursor-pointer">
                  {{ article.author ? article.author.name.charAt(0) : 'U' }}
                </div>
                <div class="flex-1 leading-tight">
                  <h4 class="font-bold text-sm text-slate-800 hover:text-blue-600 hover:underline cursor-pointer transition">
                    {{ article.author ? article.author.name : 'Unknown User' }}
                  </h4>
                  <p class="text-[11px] font-semibold text-slate-500 mt-0.5">{{ article.category }}</p>
                  
                  <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                    {{ timeAgo(article.created_at) }} 
                    <span class="text-[8px]">•</span> 
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd" /></svg>
                  </p>
                </div>

                <button 
                  v-if="article.user_id === currentUser.id" 
                  @click="deletePost(article.id)" 
                  class="text-slate-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition absolute right-4 top-4 md:opacity-0 md:group-hover:opacity-100"
                  title="Hapus Postingan"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                </button>
              </div>
              
              <div class="px-4 pb-3">
                <h3 v-if="article.title" class="font-bold text-slate-800 mb-2 text-base">{{ article.title }}</h3>
                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ article.excerpt }}</p>
              </div>

              <div v-if="article.media_url" class="w-full mt-2 bg-[#F3F2EF] flex justify-center cursor-pointer">
                <img v-if="article.media_type === 'image'" :src="article.media_url" alt="Media File" loading="lazy" class="w-full h-auto max-h-[600px] object-cover sm:object-contain" />
                <video v-else-if="article.media_type === 'video'" :src="article.media_url" controls class="w-full h-auto max-h-[600px] bg-black"></video>
              </div>

              <div class="px-4 py-2.5 flex justify-between text-xs text-slate-500 border-t border-slate-100">
                <div class="flex items-center gap-1">
                  <div v-if="article.likes_count > 0" class="w-4 h-4 rounded-full bg-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-white" viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" /></svg>
                  </div>
                  <span v-if="article.likes_count > 0">{{ article.likes_count }}</span>
                </div>
                
                <span v-if="article.comments_count > 0" class="hover:text-blue-600 hover:underline cursor-pointer" @click="toggleComments(article)">
                  {{ article.comments_count }} Komentar
                </span>
              </div>

              <div class="px-2 py-1 flex justify-around border-t border-slate-100">
                <button @click="toggleLike(article)" :class="{'text-blue-600': article.is_liked_by_me, 'text-slate-600': !article.is_liked_by_me}" class="flex-1 flex items-center justify-center gap-2 text-sm font-semibold hover:bg-slate-100 py-3 rounded-md transition transform active:scale-95">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :fill="article.is_liked_by_me ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" /></svg>
                  Suka
                </button>
                <button @click="toggleComments(article)" class="flex-1 flex items-center justify-center gap-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 py-3 rounded-md transition transform active:scale-95">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                  Komentar
                </button>
                <button class="flex-1 flex items-center justify-center gap-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 py-3 rounded-md transition transform active:scale-95 hidden sm:flex">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                  Bagikan
                </button>
              </div>

              <transition name="slide-fade">
                <div v-if="article.showComments" class="bg-slate-50 border-t border-slate-100 p-4 space-y-4">
                  
                  <div v-if="article.isCommentsLoading" class="flex justify-center py-4">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  </div>

                  <div v-else-if="article.commentList.length > 0">
                    <div v-for="comment in article.commentList" :key="comment.id" class="flex gap-3 mb-3">
                      <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center shrink-0 text-xs font-bold uppercase text-slate-500 border border-slate-300">
                        {{ comment.user_name.charAt(0) }}
                      </div>
                      <div class="bg-white px-3 py-2 rounded-r-xl rounded-bl-xl border border-slate-200 shadow-sm flex-1">
                        <div class="flex justify-between items-center mb-0.5">
                          <p class="font-bold text-xs text-slate-800 hover:underline cursor-pointer">{{ comment.user_name }}</p>
                          <p class="text-[10px] text-slate-400">{{ timeAgo(comment.created_at) }}</p>
                        </div>
                        <p class="text-sm text-slate-700 leading-snug">{{ comment.content }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <div v-else-if="article.comments_count === 0" class="text-center text-xs text-slate-400 pb-2">
                    Jadilah yang pertama berkomentar.
                  </div>

                  <div class="flex gap-2 sm:gap-3 items-start mt-2">
                    <div class="w-8 h-8 bg-blue-50 text-blue-600 border border-blue-100 rounded-full flex items-center justify-center shrink-0 font-bold text-sm">
                      {{ currentUser.initial || 'U' }}
                    </div>
                    <div class="flex-1 flex flex-col sm:flex-row gap-2 border border-slate-300 rounded-lg sm:rounded-full bg-white px-1 sm:px-4 py-1 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition-all">
                      <input v-model="article.newCommentText" @keyup.enter="submitComment(article)" type="text" placeholder="Tambahkan komentar..." class="flex-1 bg-transparent border-none px-3 py-1.5 text-sm focus:outline-none focus:ring-0" />
                      <button @click="submitComment(article)" :disabled="!article.newCommentText.trim()" class="text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:bg-slate-300 px-4 py-1.5 rounded-full font-semibold text-xs transition m-1 sm:m-0">
                        Kirim
                      </button>
                    </div>
                  </div>

                </div>
              </transition>

            </div>
          </TransitionGroup>
          
          <div v-if="articles.length > 0 && !isLoading" class="text-center py-6 pb-12 text-sm text-slate-500 flex items-center justify-center gap-2">
            <div class="h-px bg-slate-300 w-12"></div>
            Anda sudah melihat semua postingan terbaru
            <div class="h-px bg-slate-300 w-12"></div>
          </div>
        </div>

        <div class="lg:col-span-3 space-y-4 hidden lg:block">
          
          <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 sticky top-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center justify-between text-sm">
              Berita Populer Kerjapro
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 cursor-pointer hover:text-slate-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
            </h3>
            <ul class="space-y-4">
              <li class="cursor-pointer group flex gap-2.5 items-start">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-2 shrink-0 group-hover:bg-blue-500 transition"></div>
                <div>
                  <p class="text-sm font-semibold text-slate-700 group-hover:text-blue-600 group-hover:underline leading-tight">Implementasi SIMRS Terkini</p>
                  <p class="text-xs text-slate-500 mt-0.5">Kesehatan • 3.210 pembaca</p>
                </div>
              </li>
              <li class="cursor-pointer group flex gap-2.5 items-start">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-2 shrink-0 group-hover:bg-blue-500 transition"></div>
                <div>
                  <p class="text-sm font-semibold text-slate-700 group-hover:text-blue-600 group-hover:underline leading-tight">Teknik Laravel API Authentication</p>
                  <p class="text-xs text-slate-500 mt-0.5">Programming • 2.504 pembaca</p>
                </div>
              </li>
            </ul>
            <button class="mt-5 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-100 px-3 py-1.5 rounded flex items-center gap-1 transition -ml-2">
              Tampilkan lebih banyak <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
          </div>
          
          <div class="text-center px-4 mt-6">
            <p class="text-[11px] text-slate-500 leading-relaxed">
              <a href="#" class="hover:text-blue-600 hover:underline mr-2">Tentang</a>
              <a href="#" class="hover:text-blue-600 hover:underline mr-2">Aksesibilitas</a>
              <a href="#" class="hover:text-blue-600 hover:underline mr-2">Pusat Bantuan</a><br>
              <a href="#" class="hover:text-blue-600 hover:underline mr-2">Privasi & Syarat</a>
              <a href="#" class="hover:text-blue-600 hover:underline">Opsi Iklan</a>
            </p>
            <p class="text-xs text-slate-600 font-semibold mt-3">Kerjapro Corporation © 2026</p>
          </div>
        </div>

      </div>
    </div>

    <transition name="modal">
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
        
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all flex flex-col max-h-[90vh] sm:max-h-[85vh] relative z-10">
          
          <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
            <h3 class="text-xl font-semibold text-slate-800">Buat Postingan</h3>
            <button @click="closeModal" class="text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200 p-1.5 focus:outline-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <div class="p-6 space-y-4 overflow-y-auto custom-scrollbar">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-14 h-14 bg-blue-50 border border-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 font-bold text-2xl">
                {{ currentUser.initial || 'U' }}
              </div>
              <div>
                <p class="font-bold text-slate-800">{{ currentUser.name }}</p>
                <div class="inline-flex items-center gap-1 bg-slate-100 border border-slate-200 rounded-full px-2 py-0.5 mt-1 cursor-pointer hover:bg-slate-200 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                  <select v-model="postForm.category" class="text-[11px] bg-transparent border-none p-0 focus:ring-0 text-slate-600 font-semibold cursor-pointer outline-none w-auto pr-2">
                    <option value="DISKUSI UMUM">Diskusi Umum</option>
                    <option value="PROJECT MANAGEMENT">Project Management</option>
                    <option value="LOWONGAN KERJA">Lowongan Kerja</option>
                    <option value="TIPS & TRIK">Tips & Trik</option>
                  </select>
                </div>
              </div>
            </div>

            <div>
              <input v-model="postForm.title" type="text" placeholder="Judul Postingan (Opsional)" class="w-full font-semibold text-slate-800 border-none focus:ring-0 px-0 placeholder-slate-400 text-lg bg-transparent" />
            </div>
            
            <div>
              <textarea v-model="postForm.excerpt" rows="5" placeholder="Apa yang ingin Anda bagikan atau diskusikan?" class="w-full border-none focus:ring-0 px-0 text-slate-700 resize-none bg-transparent placeholder-slate-400 text-base leading-relaxed"></textarea>
            </div>

            <transition name="fade">
              <div v-if="mediaPreview" class="relative mt-2 rounded-lg overflow-hidden bg-slate-50 border border-slate-200 flex justify-center p-2 group">
                <button @click="removeSelectedMedia" type="button" class="absolute top-2 right-2 bg-slate-900/70 hover:bg-slate-900 text-white rounded-full p-2 transition z-10 shadow-lg md:opacity-0 md:group-hover:opacity-100" title="Hapus Media">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <img v-if="mediaFile?.type.startsWith('image/')" :src="mediaPreview" class="max-h-72 object-contain rounded" />
                <video v-else-if="mediaFile?.type.startsWith('video/')" :src="mediaPreview" controls class="max-h-72 rounded"></video>
              </div>
            </transition>
          </div>

          <div class="px-6 py-3 flex items-center gap-2 text-slate-600 shrink-0 border-t border-slate-100 bg-white">
            <input type="file" ref="fileInput" class="hidden" accept="image/jpeg,image/png,image/jpg,image/gif,video/mp4,video/quicktime" @change="handleFileChange" />
            
            <button @click="triggerFileInput" type="button" class="p-2.5 rounded-full hover:bg-slate-100 transition flex items-center justify-center text-slate-500" title="Lampirkan Foto/Video">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" /></svg>
            </button>
            </div>

          <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white shrink-0 rounded-b-xl">
            <button @click="closeModal" type="button" class="px-6 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-full transition">
              Batal
            </button>
            <button @click="submitPost" :disabled="!postForm.excerpt.trim() || isSubmitting" class="px-8 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-full transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-sm">
              <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              {{ isSubmitting ? 'Memposting...' : 'Posting' }}
            </button>
          </div>

        </div>
      </div>
    </transition>

  </div>
</template>

<style scoped>
/* Transisi List Animasi saat Postingan dihapus / ditambah */
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Transisi Modal Popup */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-active > div:nth-child(2) {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-enter-from > div:nth-child(2) {
  transform: translateY(20px) scale(0.95);
  opacity: 0;
}

/* Transisi Komentar (Slide Fade) */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* Transisi Standar (Fade) */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Custom Scrollbar agar Modal elegan */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: #94a3b8;
}
</style>