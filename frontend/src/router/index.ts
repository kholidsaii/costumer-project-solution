import { createRouter, createWebHistory } from 'vue-router';

// Layouts
import CustomerLayout from '../layouts/CustomerLayout.vue';
import DashboardCustomerLayout from '../layouts/DashboardCustomerLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';

// Public Components
import CustomerHome from '../components/customer/CustomerHome.vue';
import CustomerProducts from '../components/customer/CustomerProducts.vue';
import ProductDetail from '../components/customer/ProductDetail.vue'; 
import CustomerAbout from '../components/customer/CustomerAbout.vue';
import CustomerMedia from '../components/customer/CustomerMedia.vue';
import CustomerSupport from '../components/customer/CustomerSupport.vue';

// Customer Dashboard
import CustomerDashboard from '../components/dashboardcustomer/Dashboard.vue';
import CustomerProductPortal from '../components/dashboardcustomer/ProductPortal.vue';
import CustomerOrders from '../components/dashboardcustomer/Orders.vue';
import CustomerBilling from '../components/dashboardcustomer/Billing.vue';
import CustomerMember from '../components/dashboardcustomer/Member.vue';

// Admin Dashboard Components
import AdminDashboard from '../components/admin/Dashboard.vue';
import AdminProducts from '../components/admin/product/Products.vue';
import AdminOrders from '../components/admin/business/AdminOrders.vue';
import AdminBillings from '../components/admin/business/AdminBillings.vue';
import AdminCustomers from '../components/admin/business/AdminCustomers.vue';

// Member Tiers
import AdminTiers from '../components/admin/member/AdminTiers.vue'; 
import AdminMembers from '../components/admin/member/AdminMembers.vue';

// Auth
import Login from '../components/auth/LoginAkses.vue';
import Register from '../components/auth/RegisterAkses.vue';
// Misal Anda memiliki komponen ForgotPassword:
// import ForgotPassword from '../components/auth/ForgotPassword.vue';

const routes = [
  {
    path: '/customer',
    component: CustomerLayout,
    children: [
      { path: '', component: CustomerHome },
      { path: 'products', component: CustomerProducts },
      { path: 'products/:slug', component: ProductDetail },
      { path: 'about', component: CustomerAbout },
      { path: 'media', component: CustomerMedia },
      { path: 'support', component: CustomerSupport },
      { path: 'login', component: Login, meta: { guestOnly: true } },
      { path: 'register', component: Register, meta: { guestOnly: true } },
      // FIX ROUTE ERROR: Menambahkan forgot password
      { path: 'forgot-password', component: { template: '<div>Halaman Forgot Password</div>' }, meta: { guestOnly: true } },
    ]
  },
  {
    path: '/dashboard/customer',
    component: DashboardCustomerLayout,
    meta: { requiresAuth: true, role: 'customer' },
    children: [
      { path: '', component: CustomerDashboard },
      { path: 'products', component: CustomerProductPortal },
      { path: 'member', component: CustomerMember },
      { path: 'orders', component: CustomerOrders },
      { path: 'billing', component: CustomerBilling },
      { path: 'support', component: { template: '<div class="p-8 text-center text-slate-400 font-bold">Halaman Tiket Support sedang dalam pengembangan.</div>' } },
    ]
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: 'dashboard', component: AdminDashboard },
      
      // --- PERUBAHAN: Pecah rute produk menjadi 3 menu ---
      { path: 'products/software', component: AdminProducts, meta: { productType: 'software', formType: 'Software' } },
      { path: 'products/digital', component: AdminProducts, meta: { productType: 'digital', formType: 'Digital' } },
      { path: 'products/physical', component: AdminProducts, meta: { productType: 'physical', formType: 'Fisik' } },
      // --------------------------------------------------
      
      { path: 'orders', component: AdminOrders },
      { path: 'billings', component: AdminBillings },
      { path: 'customers', component: AdminCustomers },
      { path: 'tiers', component: AdminTiers },
      { path: 'members', component: AdminMembers },
      { path: 'sales', redirect: '/admin/orders' },
    ]
  },
  { path: '/', redirect: '/customer' }
];

const router = createRouter({ history: createWebHistory(), routes });

// FIX VUE ROUTER: Menggunakan return value untuk menggantikan next()
router.beforeEach((to, from) => {
  const token = localStorage.getItem('access_token');
  const userRole = localStorage.getItem('user_role');

  if (to.meta.requiresAuth && !token) {
    return '/customer/login';
  } else if (to.meta.guestOnly && token) {
    return userRole === 'admin' ? '/admin/dashboard' : '/dashboard/customer';
  } else if (to.meta.requiresAuth && to.meta.role && to.meta.role !== userRole) {
    return userRole === 'admin' ? '/admin/dashboard' : '/dashboard/customer';
  }
  
  return true; // Lanjutkan navigasi
});

export default router;