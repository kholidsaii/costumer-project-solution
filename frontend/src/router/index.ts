import { createRouter, createWebHistory } from 'vue-router';

// Layouts
import CustomerLayout from '../layouts/CustomerLayout.vue';

// Customer Views
import CustomerHome from '../components/customer/CustomerHome.vue';
import CustomerOrders from '../components/customer/CustomerOrders.vue';
import CustomerBilling from '../components/customer/CustomerBilling.vue';
import CustomerSupport from '../components/customer/CustomerSupport.vue';
import CustomerProducts from '../components/customer/CustomerProducts.vue';

const routes = [
  // Tambahkan baris ini agar otomatis pindah ke /customer saat buka localhost:5173/
  { path: '/', redirect: '/customer' },

  {
    path: '/customer',
    component: CustomerLayout,
    children: [
      { path: '', name: 'customer.home', component: CustomerHome },
      { path: 'orders', name: 'customer.orders', component: CustomerOrders },
      { path: 'billing', name: 'customer.billing', component: CustomerBilling },
      { path: 'support', name: 'customer.support', component: CustomerSupport },
      { path: 'products', name: 'customer.products', component: CustomerProducts },
    ]
  },
  // Nanti rute admin bisa dibuat terpisah di path '/admin'
];

export default createRouter({
  history: createWebHistory(),
  routes
});