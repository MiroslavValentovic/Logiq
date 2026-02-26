<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import { useWorkLogsCreate } from './useWorkLogsCreate';

const props = defineProps({ projects: Array, defaultDate: String });
const { route, form, submit } = useWorkLogsCreate(props.defaultDate || '');
const hours = ref(1);
const minDate = computed(() => `${new Date().getFullYear()}-01-01`);
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h1 class="page-title">Pridať záznam</h1>
    </template>

    <div class="card max-w-2xl p-8">
      <form @submit.prevent="submit(hours)" class="space-y-6">
        <div>
          <label for="project_id" class="block text-sm font-semibold text-slate-700">Projekt</label>
          <select id="project_id" v-model="form.project_id" required class="input-field mt-1.5 border px-4 py-2.5">
            <option value="">Vyberte projekt</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <p v-if="form.errors.project_id" class="mt-1.5 text-sm text-red-600">{{ form.errors.project_id }}</p>
        </div>
        <div>
          <label for="work_date" class="block text-sm font-semibold text-slate-700">Dátum</label>
          <input id="work_date" v-model="form.work_date" type="date" :min="minDate" required class="input-field mt-1.5 border px-4 py-2.5" />
          <p v-if="form.errors.work_date" class="mt-1.5 text-sm text-red-600">{{ form.errors.work_date }}</p>
        </div>
        <div>
          <label for="hours" class="block text-sm font-semibold text-slate-700">Hodiny</label>
          <input id="hours" v-model.number="hours" type="number" min="0.25" step="0.25" required class="input-field mt-1.5 border px-4 py-2.5" />
          <p class="mt-1.5 text-sm text-slate-500">napr. 1,5 = 1 hodina 30 min</p>
          <p v-if="form.errors.minutes" class="mt-1.5 text-sm text-red-600">{{ form.errors.minutes }}</p>
        </div>
        <div>
          <label for="note" class="block text-sm font-semibold text-slate-700">Poznámka</label>
          <textarea id="note" v-model="form.note" rows="3" class="input-field mt-1.5 border px-4 py-2.5" />
          <p v-if="form.errors.note" class="mt-1.5 text-sm text-red-600">{{ form.errors.note }}</p>
        </div>
        <div class="flex gap-3 pt-2">
          <button type="submit" class="btn-primary" :disabled="form.processing">Uložiť</button>
          <Link :href="route('work-logs.index')" class="btn-secondary">Zrušiť</Link>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
