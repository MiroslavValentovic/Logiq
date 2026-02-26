<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import { useAdminWorkLogsIndex } from './useAdminWorkLogsIndex';

function formatHours(minutes) {
  if (minutes == null) return '—';
  const h = minutes / 60;
  return (Number.isInteger(h) ? h : h.toFixed(1).replace('.', ',')) + ' h';
}
const props = defineProps({ workLogs: Object, year: [String, Number], month: [String, Number] });
const { route, filterYear, filterMonth, submitFilter } = useAdminWorkLogsIndex(props);
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="page-title">Záznamy práce</h1>
        <form class="flex gap-2" @submit.prevent="submitFilter">
          <input v-model="filterYear" type="number" placeholder="Year" class="input-field w-24 border px-3 py-2" />
          <input v-model.number="filterMonth" type="number" placeholder="Month" min="1" max="12" class="input-field w-20 border px-3 py-2" />
          <button type="submit" class="btn-primary">Filter</button>
        </form>
      </div>
    </template>

    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Používateľ</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Projekt</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Hodiny</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="log in workLogs.data" :key="log.id">
              <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ (log.work_date || '').slice(0, 10) }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ log.user?.name }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ log.project?.name }}</td>
              <td class="px-4 py-3 text-sm text-slate-900">{{ formatHours(log.minutes) }}</td>
              <td class="px-4 py-3 text-right text-sm">
                <Link :href="route('admin.work-logs.show', log.id)" class="btn-ghost">Zobraziť</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="workLogs.data?.length" class="flex flex-wrap justify-end gap-1 border-t border-slate-200 bg-slate-50/50 px-4 py-3">
        <template v-for="(link, i) in workLogs.links" :key="i">
          <Link v-if="link.url" :href="link.url" class="pagination-btn" :class="link.active ? 'pagination-btn-active' : 'pagination-btn-inactive'" v-html="link.label" />
        </template>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
