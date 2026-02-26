<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import { useProjectsIndex } from './useProjectsIndex';

defineProps({ projects: Object });
const { route, showDeleteModal, deleteModalOpen, closeDeleteModal, confirmDelete } = useProjectsIndex();
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">Projekty</h1>
        <Link :href="route('projects.create')" class="btn-primary">
          Nový projekt
        </Link>
      </div>
    </template>

    <div class="card overflow-hidden">
      <div class="p-6">
        <p v-if="!projects.data?.length" class="text-slate-500">Zatiaľ žiadne projekty.</p>
        <Link v-if="!projects.data?.length" :href="route('projects.create')" class="mt-2 inline-block font-medium text-primary-600 hover:text-primary-700">Vytvorte si prvý projekt</Link>

        <table v-else class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50/80">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Názov</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Zákazník</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Priradení používatelia</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Stav</th>
              <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Akcie</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="project in projects.data" :key="project.id" class="transition hover:bg-slate-50/50">
              <td class="px-5 py-4 text-sm font-medium text-slate-900">{{ project.name }}</td>
              <td class="px-5 py-4 text-sm text-slate-500">{{ project.client_name || '—' }}</td>
              <td class="px-5 py-4 text-sm text-slate-600">{{ project.users_count ?? 0 }}</td>
              <td class="px-5 py-4">
                <span
                  :class="project.is_active ? 'bg-primary-50 text-primary-700' : 'bg-slate-100 text-slate-600'"
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                >
                  {{ project.is_active ? 'Aktívny' : 'Neaktívny' }}
                </span>
              </td>
              <td class="px-5 py-4 text-right text-sm">
                <div class="flex flex-wrap items-center justify-end gap-1">
                  <Link :href="route('projects.edit', project.id)" class="btn-ghost">Upraviť</Link>
                  <button type="button" class="btn-danger-ghost" @click="showDeleteModal(project.id)">Zmazať</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="projects.data?.length" class="mt-6 flex flex-wrap justify-end gap-1">
          <template v-for="(link, i) in projects.links" :key="i">
            <Link
              v-if="link.url"
              :href="link.url"
              class="pagination-btn"
              :class="link.active ? 'pagination-btn-active' : 'pagination-btn-inactive'"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </div>

    <!-- Modal: Zmazať projekt -->
    <Teleport to="body">
      <div v-if="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeDeleteModal">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="delete-project-modal-title">
          <h2 id="delete-project-modal-title" class="text-lg font-bold text-slate-900">Zmazať projekt</h2>
          <p class="mt-2 text-slate-600">Naozaj chcete zmazať tento projekt? Táto akcia je nevratná.</p>
          <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="btn-secondary cursor-pointer" @click="closeDeleteModal">Zrušiť</button>
            <button type="button" class="cursor-pointer rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500" @click="confirmDelete">Zmazať</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>
