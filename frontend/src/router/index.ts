import { createRouter, createWebHistory } from 'vue-router';

// Import Layouts
import CustomerLayout from '../layouts/CustomerLayout.vue';
import DashboardCustomerLayout from '../layouts/DashboardCustomerLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';

// Import Components (Frontend)
import CustomerHome from '../components/customer/CustomerHome.vue';
import CustomerProducts from '../components/customer/CustomerProducts.vue';
import ProductDetail from '../components/customer/ProductDetail.vue'; 

// Import Halaman Publik Tambahan
import CustomerAbout from '../components/customer/CustomerAbout.vue';
import CustomerMedia from '../components/customer/CustomerMedia.vue';
import CustomerSupport from '../components/customer/CustomerSupport.vue';

// Import Components (Customer Dashboard)
import CustomerDashboard from '../components/dashboardcustomer/Dashboard.vue';
import CustomerOrders from '../components/dashboardcustomer/Orders.vue';
// import CustomerBilling from '../components/dashboardcustomer/Billing.vue';

// Import Components (Admin Dashboard)
import AdminDashboard from '../components/admin/Dashboard.vue';
import AdminProducts from '../components/admin/product/Products.vue'; // CRUD

// Import Auth
import Login from '../components/auth/LoginAkses.vue';
import Register from '../components/auth/RegisterAkses.vue';

const routes = [
  /* * 1. FRONTEND / PUBLIC AREA 
   * Menggunakan CustomerLayout
   */
  {
    path: '/customer',
    component: CustomerLayout,
    children: [
      { path: '', component: CustomerHome },
      { path: 'products', component: CustomerProducts },
      { path: 'products/:slug', component: ProductDetail }, 
      { path: 'login', component: Login },
      { path: 'register', component: Register },
      
      // Rute Publik Baru yang Ditambahkan
      { path: 'about', component: CustomerAbout },
      { path: 'media', component: CustomerMedia },
      { path: 'support', component: CustomerSupport },
    ]
  },

  /* * 2. CUSTOMER DASHBOARD / PORTAL 
   * Menggunakan DashboardCustomerLayout
   * Wajib Login (meta: requiresAuth)
   */
  {
    path: '/dashboard/customer',
    component: DashboardCustomerLayout,
    meta: { requiresAuth: true, role: 'customer' },
    children: [
      { path: '', component: CustomerDashboard },
      { path: 'orders', component: CustomerOrders },
      // { path: 'billing', component: CustomerBilling },
    ]
  },

  /* * 3. ADMIN DASHBOARD 
   * Menggunakan AdminLayout
   * Wajib Login & Role Admin
   */
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: 'dashboard', component: AdminDashboard },
      { path: 'products', component: AdminProducts },
      // route admin lainnya (sales, media, setup)...
    ]
  },
  
  // Redirect root ke /customer
  { path: '/', redirect: '/customer' }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Middleware (Navigation Guard) untuk proteksi halaman
router.beforeEach((to, from) => {
  const token = localStorage.getItem('access_token');
  const userRole = localStorage.getItem('user_role'); // 'admin' atau 'customer'

  if (to.meta.requiresAuth && !token) {
    // Jika butuh login tapi belum ada token, lempar ke login
    return '/customer/login';
  } 
  
  if (to.meta.requiresAuth && to.meta.role && to.meta.role !== userRole) {
    // Jika login tapi rolenya salah (misal customer mencoba buka /admin)
    if (userRole === 'admin') {
      return '/admin/dashboard';
    } else {
      return '/dashboard/customer';
    }
  }
});

export default router;