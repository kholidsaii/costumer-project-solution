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

// Admin Dashboard Components
import AdminDashboard from '../components/admin/Dashboard.vue';
import AdminProducts from '../components/admin/product/Products.vue';
import AdminOrders from '../components/admin/business/AdminOrders.vue';
import AdminBillings from '../components/admin/business/AdminBillings.vue';
import AdminCustomers from '../components/admin/business/AdminCustomers.vue';

// Auth
import Login from '../components/auth/LoginAkses.vue';
import Register from '../components/auth/RegisterAkses.vue';

const routes = [
  {
    path: '/customer',
    component: CustomerLayout,
    children: [
      { path: '', component: CustomerHome },
      { path: 'products', component: CustomerProducts },
      { path: 'products/:slug', component: ProductDetail }, 
      { path: 'login', component: Login },
      { path: 'register', component: Register },
      { path: 'about', component: CustomerAbout },
      { path: 'media', component: CustomerMedia },
      { path: 'support', component: CustomerSupport },
    ]
  },
  {
    path: '/dashboard/customer',
    component: DashboardCustomerLayout,
    meta: { requiresAuth: true, role: 'customer' },
    children: [
      { path: '', component: CustomerDashboard },
      { path: 'products', component: CustomerProductPortal },
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
      { path: 'products', component: AdminProducts },
      { path: 'orders', component: AdminOrders },
      { path: 'billings', component: AdminBillings },
      { path: 'customers', component: AdminCustomers },
    ]
  },
  { path: '/', redirect: '/customer' }
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to, from) => {
  const token = localStorage.getItem('access_token');
  const userRole = localStorage.getItem('user_role');

  if (to.meta.requiresAuth && !token) return '/customer/login';
  if (to.meta.requiresAuth && to.meta.role && to.meta.role !== userRole) {
    return userRole === 'admin' ? '/admin/dashboard' : '/dashboard/customer';
  }
});

export default router;