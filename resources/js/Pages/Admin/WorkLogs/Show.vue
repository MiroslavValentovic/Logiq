<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import { useAdminWorkLogsShow } from './useAdminWorkLogsShow';

function formatHours(minutes) {
  if (minutes == null) return '—';
  const h = minutes / 60;
  return (Number.isInteger(h) ? h : h.toFixed(1).replace('.', ',')) + ' h';
}
defineProps({ workLog: Object });
const { route } = useAdminWorkLogsShow();
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="page-title">Záznam #{{ workLog.id }}</h1>
        <Link :href="route('admin.work-logs.index')" class="btn-secondary">Back</Link>
      </div>
    </template>

    <div class="card p-8">
      <dl class="space-y-3 text-slate-700">
        <div><dt class="font-semibold text-slate-900">Používateľ</dt><dd>{{ workLog.user?.name }} ({{ workLog.user?.email }})</dd></div>
        <div><dt class="font-semibold text-slate-900">Projekt</dt><dd>{{ workLog.project?.name }}</dd></div>
        <div><dt class="font-semibold text-slate-900">Date</dt><dd>{{ workLog.work_date }}</dd></div>
        <div><dt class="font-semibold text-slate-900">Hodiny</dt><dd>{{ formatHours(workLog.minutes) }}</dd></div>
        <div><dt class="font-semibold text-slate-900">Poznámka</dt><dd>{{ workLog.note || '—' }}</dd></div>
      </dl>
    </div>
  </AuthenticatedLayout>
</template>
