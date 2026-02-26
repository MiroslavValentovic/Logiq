<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';
import { useAdminProjectsShow } from './useAdminProjectsShow';

defineProps({ project: Object });
const { route } = useAdminProjectsShow();

const members = (project) => project?.users ?? [];

function initials(u) {
  if (u.name && typeof u.name === 'string') {
    const parts = u.name.trim().split(/\s+/);
    return (parts[0]?.[0] || '') + (parts[1]?.[0] || '');
  }
  return (u.first_name?.[0] || '') + (u.last_name?.[0] || '') || '?';
}
</script>

<template>
  <AdminLayout>
    <template #header>
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-3">
          <Link
            :href="route('admin.projects.index')"
            class="flex shrink-0 items-center justify-center rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
            aria-label="Späť na zoznam"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <div class="min-w-0">
            <h1 class="page-title truncate">{{ project.name }}</h1>
            <p v-if="project.client_name" class="mt-0.5 truncate text-sm text-slate-500">{{ project.client_name }}</p>
          </div>
        </div>
        <Link :href="route('admin.projects.index')" class="btn-secondary shrink-0">Späť na projekty</Link>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Základné údaje -->
      <div class="card overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-4">
          <h2 class="text-base font-semibold text-slate-800">Základné údaje</h2>
        </div>
        <div class="grid gap-6 p-6 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-200 bg-slate-50/50 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Zákazník</p>
            <p class="mt-1 font-medium text-slate-900">{{ project.client_name || '—' }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50/50 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Stav</p>
            <p class="mt-1">
              <span
                :class="project.is_active ? 'bg-primary-100 text-primary-800' : 'bg-slate-100 text-slate-600'"
                class="inline-flex rounded-full px-2.5 py-1 text-sm font-medium"
              >
                {{ project.is_active ? 'Aktívny' : 'Neaktívny' }}
              </span>
            </p>
          </div>
          <div class="rounded-xl border border-primary-200 bg-primary-50/50 px-5 py-4 sm:col-span-2">
            <p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Záznamy práce</p>
            <p class="mt-1 text-xl font-bold text-primary-700">{{ (project.work_logs || project.workLogs || []).length }} záznamov</p>
          </div>
        </div>
      </div>

      <!-- Členovia projektu -->
      <div class="card overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-4">
          <h2 class="text-base font-semibold text-slate-800">Členovia projektu</h2>
          <p class="mt-0.5 text-sm text-slate-500">Používatelia priradení k tomuto projektu.</p>
        </div>
        <div class="p-6">
          <p v-if="!members(project).length" class="text-slate-500">K projektu nie sú priradení žiadni členovia.</p>
          <ul v-else class="space-y-2">
            <li
              v-for="u in members(project)"
              :key="u.id"
              class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-slate-300 hover:bg-slate-50/50"
            >
              <div class="flex min-w-0 items-center gap-3">
                <span
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700"
                >
                  {{ (initials(u) || '?').toUpperCase() }}
                </span>
                <div class="min-w-0">
                  <p class="font-medium text-slate-900">{{ u.name || [u.first_name, u.last_name].filter(Boolean).join(' ') || '—' }}</p>
                  <p v-if="u.email" class="truncate text-sm text-slate-500">{{ u.email }}</p>
                </div>
              </div>
              <Link
                v-if="u.id"
                :href="route('admin.users.work-logs.index', u.id)"
                class="shrink-0 text-sm font-medium text-primary-600 hover:text-primary-700"
              >
                Kalendár →
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
