<script setup>
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';
import { useAdminUsersIndex } from './useAdminUsersIndex';

defineProps({ users: Object });
const { route, showDeleteModal, deleteModalOpen, closeDeleteModal, confirmDelete } = useAdminUsersIndex();
</script>

<template>
  <AdminLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="page-title">Používatelia</h1>
        <Link :href="route('admin.users.create')" class="btn-primary">Nový používateľ</Link>
      </div>
    </template>

    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Meno</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">E-mail</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Admin</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Akcie</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="user in users.data" :key="user.id" class="h-16">
              <td class="align-middle px-4 py-3 text-sm font-medium text-slate-900">{{ user.name }}</td>
              <td class="align-middle px-4 py-3 text-sm text-slate-600">{{ user.email }}</td>
              <td class="align-middle px-4 py-3 text-sm">{{ user.is_admin ? 'Áno' : 'Nie' }}</td>
              <td class="align-middle px-4 py-3 text-right text-sm">
                <div v-if="user.is_admin" class="flex items-center justify-end" title="Administrátor">
                  <Icon icon="mdi:shield-account" class="h-7 w-7 text-primary-600" aria-hidden="true" />
                </div>
                <div v-else class="flex flex-wrap items-center justify-end gap-1">
                  <Link :href="route('admin.users.work-logs.index', [user.id])" class="btn-ghost">Kalendár</Link>
                  <Link :href="route('admin.users.edit', user.id)" class="btn-ghost">Upraviť</Link>
                  <button v-if="user.id !== $page.props.auth.user?.id" type="button" class="btn-danger-ghost" @click="showDeleteModal(user.id)">Zmazať</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="users.data?.length" class="flex flex-wrap justify-end gap-1 border-t border-slate-200 bg-slate-50/50 px-4 py-3">
        <template v-for="(link, i) in users.links" :key="i">
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

    <!-- Modal: Zmazať používateľa -->
    <Teleport to="body">
      <div v-if="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeDeleteModal">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="delete-user-modal-title">
          <h2 id="delete-user-modal-title" class="text-lg font-bold text-slate-900">Zmazať používateľa</h2>
          <p class="mt-2 text-slate-600">Naozaj chcete zmazať tohto používateľa? Táto akcia je nevratná.</p>
          <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="btn-secondary cursor-pointer" @click="closeDeleteModal">Zrušiť</button>
            <button type="button" class="cursor-pointer rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500" @click="confirmDelete">Zmazať</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
