<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import { useProjectsEdit } from './useProjectsEdit';

const props = defineProps({ project: Object, users: Object, work_logs: { type: Array, default: () => [] } });
const usersList = computed(() => props.users?.data ?? []);
const usersLinks = computed(() => props.users?.links ?? []);
const { route, form, submit } = useProjectsEdit(props.project);

function toggleUser(userId) {
  const ids = new Set(form.user_ids || []);
  if (ids.has(userId)) ids.delete(userId);
  else ids.add(userId);
  form.user_ids = Array.from(ids);
}

function isUserChecked(userId) {
  return (form.user_ids || []).includes(userId);
}

function userDisplayName(u) {
  return [u.first_name, u.last_name].filter(Boolean).join(' ').trim() || '—';
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h1 class="page-title">Upraviť projekt</h1>
    </template>

    <div class="w-full max-w-7xl space-y-8">
      <div class="card w-full p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="name" class="block text-sm font-semibold text-slate-700">Názov</label>
            <input id="name" v-model="form.name" type="text" required class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
            <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>
          <div>
            <label for="client_name" class="block text-sm font-semibold text-slate-700">Meno zákazníka</label>
            <input id="client_name" v-model="form.client_name" type="text" class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
          </div>
          <div class="flex items-center gap-2">
            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
            <label for="is_active" class="text-sm text-slate-700">Aktívny</label>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary" :disabled="form.processing">
              Uložiť projekt
            </button>
            <Link :href="route('projects.index')" class="btn-secondary">Zrušiť</Link>
          </div>
        </form>
      </div>

      <div v-if="work_logs.length" class="card overflow-hidden p-0">
        <h2 class="border-b border-slate-200 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-800">Aktivity (záznamy práce)</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-6 py-2 text-left text-xs font-medium uppercase text-slate-500">Dátum</th>
                <th class="px-6 py-2 text-left text-xs font-medium uppercase text-slate-500">Používateľ</th>
                <th class="px-6 py-2 text-right text-xs font-medium uppercase text-slate-500">Minúty</th>
                <th class="px-6 py-2 text-left text-xs font-medium uppercase text-slate-500">Poznámka</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="w in work_logs" :key="w.id" class="text-sm">
                <td class="whitespace-nowrap px-6 py-3 text-slate-900">{{ w.work_date }}</td>
                <td class="px-6 py-3 text-slate-700">{{ w.user_name ?? '—' }}</td>
                <td class="whitespace-nowrap px-6 py-3 text-right text-slate-700">{{ w.minutes }}</td>
                <td class="max-w-[14rem] break-words px-6 py-3 text-sm text-slate-600 whitespace-normal">{{ w.note ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="usersList.length || usersLinks.length" class="card overflow-hidden p-0">
        <h2 class="border-b border-slate-200 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-800">Priradení používatelia</h2>
        <p v-if="form.errors.user_ids" class="border-b border-red-100 bg-red-50/50 px-6 py-2 text-sm text-red-600">{{ form.errors.user_ids }}</p>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="w-12 px-4 py-2 text-left text-xs font-medium uppercase text-slate-500">Priradiť</th>
                <th class="px-6 py-2 text-left text-xs font-medium uppercase text-slate-500">Meno</th>
                <th class="px-6 py-2 text-left text-xs font-medium uppercase text-slate-500">E-mail</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="u in usersList" :key="u.id" class="text-sm transition hover:bg-slate-50/50">
                <td class="px-4 py-3">
                  <input
                    type="checkbox"
                    :checked="isUserChecked(u.id)"
                    class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    @change="toggleUser(u.id)"
                  />
                </td>
                <td class="px-6 py-3 text-slate-900">{{ userDisplayName(u) }}</td>
                <td class="px-6 py-3 text-slate-600">{{ u.email ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="usersLinks.length" class="flex flex-wrap justify-end gap-1 border-t border-slate-200 bg-slate-50/50 px-4 py-3">
          <template v-for="(link, i) in usersLinks" :key="i">
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
  </AuthenticatedLayout>
</template>
