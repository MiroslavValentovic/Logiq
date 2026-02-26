<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  notifications: Object,
});

const route = (name, ...params) => window.route(name, ...params);

function formatDate(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleDateString('sk-SK', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function monthLabelSk(monthYmd) {
  if (!monthYmd) return '';
  const [y, m] = monthYmd.split('-').map(Number);
  const d = new Date(y, m - 1, 1);
  const s = d.toLocaleDateString('sk-SK', { month: 'long', year: 'numeric' });
  return s.charAt(0).toUpperCase() + s.slice(1);
}

function message(notification) {
  const data = notification.data || {};
  if (data.type === 'unlock_request_reviewed' && data.month_ymd) {
    const monthLabel = monthLabelSk(data.month_ymd);
    return data.approved
      ? `Požiadavka na odomknutie výkazu za ${monthLabel} bola schválená. Môžete znova upravovať záznamy.`
      : `Požiadavka na odomknutie výkazu za ${monthLabel} bola zamietnutá.`;
  }
  return data.message || 'Notifikácia';
}

function isApproved(notification) {
  return (notification.data || {}).approved === true;
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h1 class="page-title">Notifikácie</h1>
    </template>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <ul class="divide-y divide-slate-200">
        <li v-if="!notifications?.data?.length" class="px-4 py-12 text-center text-slate-500">
          Nemáte žiadne notifikácie.
        </li>
        <li
          v-for="n in notifications?.data ?? []"
          :key="n.id"
          class="flex items-start gap-4 px-4 py-4 transition hover:bg-slate-50/70 sm:px-6"
          :class="!n.read_at ? 'bg-primary-50/50' : ''"
        >
          <span
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white"
            :class="isApproved(n) ? 'bg-primary-600' : 'bg-red-500'"
          >
            <svg v-if="isApproved(n)" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </span>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-slate-900">
              {{ message(n) }}
            </p>
            <p class="mt-0.5 text-xs text-slate-500">{{ formatDate(n.created_at) }}</p>
          </div>
        </li>
      </ul>
      <div v-if="notifications?.data?.length" class="flex flex-wrap justify-end gap-1 border-t border-slate-200 bg-slate-50/50 px-4 py-3">
        <Link
          v-for="(link, i) in notifications.links"
          :key="i"
          :href="link.url"
          class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
          :class="link.active ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-200 hover:text-slate-900'"
          v-html="link.label"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
