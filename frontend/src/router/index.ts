import { createRouter, createWebHistory } from 'vue-router';

// Layouts
import CustomerLayout from '../layouts/CustomerLayout.vue';
import DashboardCustomerLayout from '../layouts/DashboardCustomerLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue'; // <-- Tambahkan Import ini

// Public Customer Views
import CustomerHome from '../components/customer/CustomerHome.vue';
import CustomerSupport from '../components/customer/CustomerSupport.vue';
import CustomerProducts from '../components/customer/CustomerProducts.vue';
import CustomerMedia from '../components/customer/CustomerMedia.vue';
import CustomerAbout from '../components/customer/CustomerAbout.vue'; 

// Auth Views
import LoginAkses from '../components/auth/LoginAkses.vue'; 
import RegisterAkses from '../components/auth/RegisterAkses.vue'; 

// --- Tambahkan Import Komponen Dashboard Baru ---
import DashboardCustomer from '../components/dashboardcustomer/Dashboard.vue';
import CustomerOrders from '../components/customer/CustomerOrders.vue';
import CustomerBilling from '../components/customer/CustomerBilling.vue';

const routes = [
  { path: '/', redirect: '/customer' },

  /*
  |--------------------------------------------------------------------------
  | 1. PUBLIC ROUTES GROUP (Menggunakan Header Banner Besar)
  |--------------------------------------------------------------------------
  */
  {
    path: '/customer',
    component: CustomerLayout,
    children: [
      { path: '', name: 'customer.home', component: CustomerHome },
      { path: 'support', name: 'customer.support', component: CustomerSupport },
      { path: 'products', name: 'customer.products', component: CustomerProducts },
      { path: 'media', name: 'customer.media', component: CustomerMedia },
      { path: 'about', name: 'customer.about', component: CustomerAbout }, 
      { path: 'login', name: 'login', component: LoginAkses },
      { path: 'register', name: 'register', component: RegisterAkses },
    ]
  },
  
  /*
  |--------------------------------------------------------------------------
  | 2. PRIVAT CUSTOMER DASHBOARD GROUP (Menggunakan Sidebar Panel Kiri)
  |--------------------------------------------------------------------------
  */
  {
    path: '/dashboard/customer',
    component: DashboardCustomerLayout,
    meta: { requiresAuth: true, role: 'customer' }, // Diproteksi wajib login & harus customer
    children: [
      { path: '', name: 'customer.dashboard', component: DashboardCustomer },
      { path: 'orders', name: 'customer.orders', component: CustomerOrders },
      { path: 'billing', name: 'customer.billing', component: CustomerBilling },
    ]
  },

  /*
  |--------------------------------------------------------------------------
  | 3. ADMINISTRATOR GROUP (Menggunakan Sidebar Panel Kiri Hitam)
  |--------------------------------------------------------------------------
  */
  {
    path: '/admin',
    component: AdminLayout, // Menggunakan Layout Sidebar Admin Baru kita
    meta: { requiresAuth: true, role: 'admin' }, // Kunci hak akses wajib admin
    children: [
      { 
        path: 'dashboard', 
        name: 'admin.dashboard', 
        component: () => import('../components/admin/Dashboard.vue') 
      },
      { 
        path: 'sales', 
        name: 'admin.sales', 
        component: { template: '<div class="p-6 bg-white rounded-xl shadow-sm border border-slate-200"><h1 class="text-2xl font-black text-slate-800">Manajemen Sales & Marketing Leads</h1><p class="text-slate-400 text-sm mt-1">Daftar leads dari tabel marketing_leads akan ditarik ke sini.</p></div>' } 
      },
      { 
        path: 'products', 
        name: 'admin.products', 
        component: { template: '<div class="p-6 bg-white rounded-xl shadow-sm border border-slate-200"><h1 class="text-2xl font-black text-slate-800">Kelola Produk & Solusi</h1><p class="text-slate-400 text-sm mt-1">Form untuk menambah/menghapus produk portal customer.</p></div>' } 
      },
      { 
        path: 'media', 
        name: 'admin.media', 
        component: { template: '<div class="p-6 bg-white rounded-xl shadow-sm border border-slate-200"><h1 class="text-2xl font-black text-slate-800">Manajemen Berita & Artikel Media</h1><p class="text-slate-400 text-sm mt-1">Gunakan halaman ini untuk memposting artikel baru.</p></div>' } 
      },
      { 
        path: 'support', 
        name: 'admin.support', 
        component: { template: '<div class="p-6 bg-white rounded-xl shadow-sm border border-slate-200"><h1 class="text-2xl font-black text-slate-800">Pusat Tiket Kendala Pelanggan</h1><p class="text-slate-400 text-sm mt-1">Balas tiket keluhan dan kelola tutorial sistem.</p></div>' } 
      },
      { 
        path: 'setup', 
        name: 'admin.setup', 
        component: { template: '<div class="p-6 bg-white rounded-xl shadow-sm border border-slate-200"><h1 class="text-2xl font-black text-slate-800">Konfigurasi Pengaturan Sistem</h1><p class="text-slate-400 text-sm mt-1">Pengaturan website umum, user access role, dan konfigurasi payment.</p></div>' } 
      },
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Router Guard (Sistem Otentikasi & Validasi Role)
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('access_token');
  const userRole = localStorage.getItem('user_role');

  if (to.meta.requiresAuth) {
    if (!token) return next({ name: 'login' });
    if (to.meta.role && to.meta.role !== userRole) {
      return userRole === 'admin' ? next({ name: 'admin.dashboard' }) : next({ name: 'customer.dashboard' });
    }
  }

  if ((to.name === 'login' || to.name === 'register') && token) {
    return userRole === 'admin' ? next({ name: 'admin.dashboard' }) : next({ name: 'customer.dashboard' });
  }

  next();
});

export default router;