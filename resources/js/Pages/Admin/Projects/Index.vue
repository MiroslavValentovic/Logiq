<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';
import { useAdminProjectsIndex } from './useAdminProjectsIndex';

defineProps({ projects: Object });
const { route } = useAdminProjectsIndex();
</script>

<template>
  <AdminLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">Projekty</h1>
        <Link :href="route('projects.create')" class="btn-primary">Nový projekt</Link>
      </div>
    </template>

    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Názov</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Zákazník</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Členovia</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Stav</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Akcie</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="project in projects.data" :key="project.id">
              <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ project.name }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ project.client_name || '—' }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ (project.users || []).length }} {{ (project.users || []).length === 1 ? 'člen' : 'členovia' }}</td>
              <td class="px-4 py-3">
                <span :class="project.is_active ? 'bg-primary-100 text-primary-800' : 'bg-slate-100 text-slate-600'" class="rounded-full px-2.5 py-1 text-xs font-medium">{{ project.is_active ? 'Aktívny' : 'Neaktívny' }}</span>
              </td>
              <td class="px-4 py-3 text-right text-sm">
                <Link :href="route('admin.projects.show', project.id)" class="font-medium text-primary-600 hover:text-primary-700">Zobraziť</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="projects.data?.length" class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-4 py-3">
        <template v-for="(link, i) in projects.links" :key="i">
          <Link v-if="link.url" :href="link.url" class="rounded-lg px-3 py-1.5 text-sm font-medium transition" :class="link.active ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-200 hover:text-slate-900'" v-html="link.label" />
        </template>
      </div>
    </div>
  </AdminLayout>
</template>
