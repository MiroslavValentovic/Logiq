<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';

const props = defineProps({
  requests: Object,
  filterStatus: { type: String, default: 'pending' },
});

const route = (name, ...params) => window.route(name, ...params);
const submittingId = ref(null);

function approve(req) {
  if (submittingId.value) return;
  submittingId.value = req.id;
  router.post(route('admin.unlock-requests.approve', req.id), {}, {
    preserveScroll: true,
    onFinish: () => { submittingId.value = null; },
  });
}

function reject(req) {
  if (submittingId.value) return;
  submittingId.value = req.id;
  router.post(route('admin.unlock-requests.reject', req.id), {}, {
    preserveScroll: true,
    onFinish: () => { submittingId.value = null; },
  });
}

function formatDate(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleDateString('sk-SK', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatMonth(value) {
  if (value == null) return '—';
  const str = typeof value === 'string' ? value : value?.date ?? value;
  if (!str) return '—';
  const datePart = String(str).slice(0, 10);
  if (!/^\d{4}-\d{2}-\d{2}$/.test(datePart)) return '—';
  const d = new Date(datePart + 'T12:00:00');
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleDateString('sk-SK', { month: 'long', year: 'numeric' });
}

const statusOptions = [
  { value: 'pending', label: 'Čakajúce' },
  { value: 'approved', label: 'Schválené' },
  { value: 'rejected', label: 'Zamietnuté' },
];

function reportHref(req) {
  const m = typeof req.month === 'string' ? req.month : req.month?.date ?? req.month;
  if (!m) return '#';
  const monthStr = m.toString().slice(0, 10);
  const base = route('reports.show', monthStr);
  return req.user_id ? `${base}?user_id=${req.user_id}` : base;
}
</script>

<template>
  <AdminLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">Požiadavky na odomknutie</h1>
      </div>
    </template>

    <div class="space-y-6">
      <div class="flex flex-wrap items-center gap-3">
        <span class="text-sm font-medium text-slate-600">Filter:</span>
        <Link
          v-for="opt in statusOptions"
          :key="opt.value"
          :href="route('admin.unlock-requests.index', { status: opt.value })"
          class="rounded-xl border px-4 py-2 text-sm font-medium transition"
          :class="filterStatus === opt.value
            ? 'border-primary-500 bg-primary-50 text-primary-700'
            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
        >
          {{ opt.label }}
        </Link>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50/80">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Používateľ</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Mesiac</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Stav</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Dátum požiadavky</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Akcie</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-if="!requests?.data?.length" class="bg-white">
                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                  Žiadne požiadavky.
                </td>
              </tr>
              <tr
                v-for="req in requests?.data ?? []"
                :key="req.id"
                class="transition hover:bg-slate-50/70"
              >
                <td class="px-4 py-3">
                  <span class="font-medium text-slate-900">{{ req.user?.first_name }} {{ req.user?.last_name }}</span>
                  <p class="text-xs text-slate-500">{{ req.user?.email }}</p>
                </td>
                <td class="px-4 py-3 text-sm text-slate-700">
                  {{ formatMonth(req.month) }}
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="{
                      'bg-amber-100 text-amber-800': req.status === 'pending',
                      'bg-primary-100 text-primary-800': req.status === 'approved',
                      'bg-slate-100 text-slate-600': req.status === 'rejected',
                    }"
                  >
                    {{ req.status === 'pending' ? 'Čaká' : req.status === 'approved' ? 'Schválené' : 'Zamietnuté' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                  {{ formatDate(req.created_at) }}
                </td>
                <td class="px-4 py-3 text-right text-sm">
                  <div class="flex flex-wrap items-center justify-end gap-2">
                    <Link
                      v-if="req.user_id"
                      :href="reportHref(req)"
                      class="btn-ghost rounded-lg inline-flex"
                    >
                      Výkaz
                    </Link>
                    <template v-if="req.status === 'pending'">
                      <button
                        type="button"
                        class="btn-primary rounded-lg px-3 py-1.5 text-sm"
                        :disabled="submittingId === req.id"
                        @click="approve(req)"
                      >
                        {{ submittingId === req.id ? '…' : 'Schváliť' }}
                      </button>
                      <button
                        type="button"
                        class="btn-secondary rounded-lg border-red-200 bg-red-50 px-3 py-1.5 text-sm text-red-700 hover:bg-red-100"
                        :disabled="submittingId === req.id"
                        @click="reject(req)"
                      >
                        {{ submittingId === req.id ? '…' : 'Zamietnúť' }}
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="requests?.data?.length" class="flex flex-wrap justify-end gap-1 border-t border-slate-200 bg-slate-50/50 px-4 py-3">
          <template v-for="(link, i) in requests.links" :key="i">
            <Link
              v-if="link.url"
              :href="link.url"
              class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
              :class="link.active ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-200 hover:text-slate-900'"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
