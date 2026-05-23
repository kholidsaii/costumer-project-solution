<script setup lang="ts">
import { onMounted } from 'vue';
import { useApi } from '../../composables/useApi'; // Impor composable

interface Lead {
  id: number;
  lead_name: string;
  company_name: string;
  phone: string;
  status: string;
  estimated_value: number;
}

// Inisialisasi composable
const { data: leads, isLoading, request } = useApi<Lead[]>();

const fetchLeads = () => request('get', '/marketing-leads');

onMounted(fetchLeads);
</script>

<template>
  <div class="p-8">
    <div v-if="isLoading" class="text-center">Memuat data...</div>
    
    <div v-else class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
      <table class="w-full text-left">
        <tbody class="divide-y divide-slate-50">
          <tr v-for="lead in leads" :key="lead.id" class="text-[11px] font-bold text-slate-700 hover:bg-slate-50">
            <td class="py-4">{{ lead.lead_name }}</td>
            <td class="py-4 text-slate-500">{{ lead.company_name }}</td>
            <td class="py-4">{{ lead.phone }}</td>
            <td class="py-4">
              <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-600 uppercase">{{ lead.status }}</span>
            </td>
            <td class="py-4 font-mono">Rp {{ Number(lead.estimated_value).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>