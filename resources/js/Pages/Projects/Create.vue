<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import UserCheckboxList from '@/Components/UserCheckboxList.vue';
import { useProjectsCreate } from './useProjectsCreate';

defineProps({ users: { type: Array, default: () => [] } });
const { route, form, submit } = useProjectsCreate();
const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin ?? false);
const cancelHref = computed(() => (isAdmin.value ? route('admin.projects.index') : route('projects.index')));
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h1 class="page-title">Nový projekt</h1>
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
            <label for="client_name" class="block text-sm font-semibold text-slate-700">Zákazník</label>
            <input id="client_name" v-model="form.client_name" type="text" class="input-field mt-1.5 w-full max-w-md border px-4 py-2.5" />
          </div>
          <div v-if="users.length">
            <UserCheckboxList v-model="form.user_ids" :users="users" label="Priradiť používateľov k projektu" />
            <p v-if="form.errors.user_ids" class="mt-1.5 text-sm text-red-600">{{ form.errors.user_ids }}</p>
          </div>
          <div class="flex items-center gap-2">
            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
            <label for="is_active" class="text-sm text-slate-700">Aktívny</label>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary" :disabled="form.processing">
              Vytvoriť projekt
            </button>
            <Link :href="cancelHref" class="btn-secondary">Zrušiť</Link>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
