<script setup>
import { watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';
import ProjectCheckboxList from '@/Components/ProjectCheckboxList.vue';
import { useAdminUsersCreate } from './useAdminUsersCreate';

defineProps({ projects: { type: Array, default: () => [] } });
const { route, form, submit } = useAdminUsersCreate();

watch(
  () => form.is_admin,
  (isAdmin) => {
    if (isAdmin) form.project_ids = [];
  }
);
</script>

<template>
  <AdminLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">Nový používateľ</h1>
        <label class="flex cursor-pointer select-none items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
          <input v-model="form.is_admin" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
          Administrátor
        </label>
      </div>
    </template>

    <div class="w-full max-w-7xl space-y-8">
      <div class="card w-full p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="first_name" class="block text-sm font-semibold text-slate-700">Meno</label>
            <input id="first_name" v-model="form.first_name" type="text" required class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
            <p v-if="form.errors.first_name" class="mt-1.5 text-sm text-red-600">{{ form.errors.first_name }}</p>
          </div>
          <div v-if="!form.is_admin">
            <label for="last_name" class="block text-sm font-semibold text-slate-700">Priezvisko</label>
            <input id="last_name" v-model="form.last_name" type="text" :required="!form.is_admin" class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
            <p v-if="form.errors.last_name" class="mt-1.5 text-sm text-red-600">{{ form.errors.last_name }}</p>
          </div>
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">E-mail</label>
            <input id="email" v-model="form.email" type="email" required class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
            <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>
          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700">Heslo</label>
            <input id="password" v-model="form.password" type="password" required class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
            <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>
          <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Potvrdiť heslo</label>
            <input id="password_confirmation" v-model="form.password_confirmation" type="password" required class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
          </div>
          <div v-if="projects.length && !form.is_admin">
            <ProjectCheckboxList v-model="form.project_ids" :projects="projects" label="Priradené projekty" />
            <p v-if="form.errors.project_ids" class="mt-1.5 text-sm text-red-600">{{ form.errors.project_ids }}</p>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary" :disabled="form.processing">Vytvoriť používateľa</button>
            <Link :href="route('admin.users.index')" class="btn-secondary">Zrušiť</Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
