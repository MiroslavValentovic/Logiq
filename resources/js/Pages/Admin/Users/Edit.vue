<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';
import { useAdminUsersEdit } from './useAdminUsersEdit';

const props = defineProps({ user: Object, projects: Object });
const projectsList = computed(() => props.projects?.data ?? []);
const projectsLinks = computed(() => props.projects?.links ?? []);
const { route, form, submit } = useAdminUsersEdit(props.user);

function toggleProject(projectId) {
  const ids = new Set(form.project_ids || []);
  if (ids.has(projectId)) ids.delete(projectId);
  else ids.add(projectId);
  form.project_ids = Array.from(ids);
}

function isProjectChecked(projectId) {
  return (form.project_ids || []).includes(projectId);
}
</script>

<template>
  <AdminLayout>
    <template #header>
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-3">
          <Link
            :href="route('admin.users.index')"
            class="flex shrink-0 items-center justify-center rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
            aria-label="Späť na zoznam"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <div class="min-w-0">
            <h1 class="page-title truncate">Upraviť používateľa</h1>
            <p class="mt-0.5 truncate text-sm text-slate-500">{{ user?.email }}</p>
          </div>
        </div>
      </div>
    </template>

    <form @submit.prevent="submit" class="w-full space-y-8">
      <!-- Osobné údaje a účet -->
      <div class="card overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-4">
          <h2 class="text-base font-semibold text-slate-800">Osobné údaje a účet</h2>
          <p class="mt-0.5 text-sm text-slate-500">Meno, e‑mail a rola v systéme.</p>
        </div>
        <div class="space-y-6 p-6">
          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <label for="first_name" class="block text-sm font-medium text-slate-700">Meno</label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
                autocomplete="given-name"
                class="input-field mt-1.5 border-slate-300 px-4 py-2.5"
              />
              <p v-if="form.errors.first_name" class="mt-1.5 text-sm text-red-600">{{ form.errors.first_name }}</p>
            </div>
            <div v-if="!form.is_admin">
              <label for="last_name" class="block text-sm font-medium text-slate-700">Priezvisko</label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                :required="!form.is_admin"
                autocomplete="family-name"
                class="input-field mt-1.5 border-slate-300 px-4 py-2.5"
              />
              <p v-if="form.errors.last_name" class="mt-1.5 text-sm text-red-600">{{ form.errors.last_name }}</p>
            </div>
          </div>
          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <label for="email" class="block text-sm font-medium text-slate-700">E‑mail</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                class="input-field mt-1.5 border-slate-300 px-4 py-2.5"
              />
              <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3">
            <input
              id="is_admin"
              v-model="form.is_admin"
              type="checkbox"
              class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
            />
            <label for="is_admin" class="text-sm font-medium text-slate-700">Administrátor</label>
            <span class="text-sm text-slate-500">— plný prístup, bez priradených projektov</span>
          </div>
        </div>
      </div>

      <!-- Heslo -->
      <div class="card overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-4">
          <h2 class="text-base font-semibold text-slate-800">Heslo</h2>
          <p class="mt-0.5 text-sm text-slate-500">Vyplňte len ak chcete zmeniť heslo používateľa.</p>
        </div>
        <div class="space-y-6 p-6">
          <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Nové heslo</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              placeholder="Nechajte prázdne ak nemeníte"
              class="input-field mt-1.5 max-w-md border-slate-300 px-4 py-2.5 placeholder:text-slate-400"
            />
            <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>
          <div v-if="form.password">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Potvrdiť nové heslo</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              autocomplete="new-password"
              class="input-field mt-1.5 max-w-md border-slate-300 px-4 py-2.5"
            />
            <p v-if="form.errors.password_confirmation" class="mt-1.5 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
          </div>
        </div>
      </div>

      <!-- Priradené projekty (len pre ne-adminov) -->
      <div v-if="!form.is_admin && (projectsList.length || projectsLinks.length)" class="card overflow-hidden p-0">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-4">
          <h2 class="text-base font-semibold text-slate-800">Priradené projekty</h2>
          <p class="mt-0.5 text-sm text-slate-500">Vyberte projekty, na ktoré má používateľ prístup.</p>
        </div>
        <p v-if="form.errors.project_ids" class="border-b border-red-100 bg-red-50/50 px-6 py-2 text-sm text-red-600">{{ form.errors.project_ids }}</p>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="w-12 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Priradiť</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Názov</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Zákazník</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="p in projectsList" :key="p.id" class="transition hover:bg-slate-50/50">
                <td class="px-6 py-3">
                  <input
                    type="checkbox"
                    :checked="isProjectChecked(p.id)"
                    class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    @change="toggleProject(p.id)"
                  />
                </td>
                <td class="px-6 py-3 text-sm font-medium text-slate-900">{{ p.name }}</td>
                <td class="px-6 py-3 text-sm text-slate-600">{{ p.client_name ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="projectsLinks.length" class="flex flex-wrap justify-end gap-1 border-t border-slate-200 bg-slate-50/50 px-4 py-3">
          <template v-for="(link, i) in projectsLinks" :key="i">
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

      <!-- Akcie -->
      <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          Uložiť zmeny
        </button>
        <Link :href="route('admin.users.index')" class="btn-secondary">Zrušiť</Link>
      </div>
    </form>
  </AdminLayout>
</template>
