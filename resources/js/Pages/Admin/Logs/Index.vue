<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';

const props = defineProps({
  activities: Object,
  users: { type: Array, default: () => [] },
  actionTypes: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const route = (name, ...params) => window.route(name, ...params);

const activityAccordionOpen = ref(true);

function formatDateTime(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleString('sk-SK', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

const hasFilters = computed(
  () =>
    props.filters?.user_id ||
    props.filters?.date_from ||
    props.filters?.date_to ||
    props.filters?.action
);
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="page-title">Logy</h1>
    </template>

    <div class="space-y-6">
      <!-- Accordion: Aktivity -->
      <section class="rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200/80">
        <button
          type="button"
          class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50/80"
          :aria-expanded="activityAccordionOpen"
          @click="activityAccordionOpen = !activityAccordionOpen"
        >
          <h2 class="text-lg font-semibold text-slate-900">Aktivity</h2>
          <svg
            class="h-5 w-5 shrink-0 text-slate-500 transition"
            :class="activityAccordionOpen && 'rotate-180'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div v-show="activityAccordionOpen" class="border-t border-slate-200">
          <!-- Filtre -->
          <form
            :action="route('admin.logs.index')"
            method="get"
            class="flex flex-wrap items-end gap-4 border-b border-slate-200 bg-slate-50/50 p-4"
          >
            <div class="min-w-0">
              <label for="filter-user" class="block text-xs font-medium text-slate-500">Používateľ</label>
              <select id="filter-user" name="user_id" class="input-field mt-1 border px-3 py-2 text-sm">
                <option value="">Všetci</option>
                <option v-for="u in users" :key="u.id" :value="u.id" :selected="filters?.user_id == u.id">
                  {{ u.name }} ({{ u.email }})
                </option>
              </select>
            </div>
            <div class="min-w-0">
              <label for="filter-date-from" class="block text-xs font-medium text-slate-500">Od dátumu</label>
              <input
                id="filter-date-from"
                name="date_from"
                type="date"
                :value="filters?.date_from ?? ''"
                class="input-field mt-1 border px-3 py-2 text-sm"
              />
            </div>
            <div class="min-w-0">
              <label for="filter-date-to" class="block text-xs font-medium text-slate-500">Do dátumu</label>
              <input
                id="filter-date-to"
                name="date_to"
                type="date"
                :value="filters?.date_to ?? ''"
                class="input-field mt-1 border px-3 py-2 text-sm"
              />
            </div>
            <div v-if="actionTypes.length" class="min-w-0">
              <label for="filter-action" class="block text-xs font-medium text-slate-500">Typ akcie</label>
              <select id="filter-action" name="action" class="input-field mt-1 border px-3 py-2 text-sm">
                <option value="">Všetky</option>
                <option v-for="a in actionTypes" :key="a" :value="a" :selected="filters?.action === a">
                  {{ a }}
                </option>
              </select>
            </div>
            <div class="flex gap-2">
              <button type="submit" class="btn-secondary rounded-xl py-2 text-sm">Filtrovať</button>
              <Link
                v-if="hasFilters"
                :href="route('admin.logs.index')"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
              >
                Zrušiť filtre
              </Link>
            </div>
          </form>

          <!-- Tabuľka -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50/80">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Dátum a čas
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Používateľ
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Akcia
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Popis
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 bg-white">
                <tr v-if="!activities?.data?.length" class="bg-white">
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">
                    Žiadne záznamy. Aktivita používateľov sa tu zobrazí po vykonaní akcií v aplikácii.
                  </td>
                </tr>
                <tr
                  v-for="log in activities?.data ?? []"
                  :key="log.id"
                  class="transition hover:bg-slate-50/70"
                >
                  <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                    {{ formatDateTime(log.created_at) }}
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <span v-if="log.user" class="font-medium text-slate-900">{{ log.user.first_name }} {{ log.user.last_name }}</span>
                    <span v-else class="text-slate-500">—</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                      {{ log.action }}
                    </span>
                  </td>
                  <td class="max-w-md px-4 py-3 text-sm text-slate-600">
                    {{ log.description }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginácia -->
          <div
            v-if="activities?.data?.length"
            class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 bg-slate-50/50 px-4 py-3"
          >
            <p class="text-sm text-slate-600">
              Zobrazených {{ activities.data.length }} z {{ activities.total }} záznamov.
            </p>
            <div class="flex flex-wrap gap-1">
              <template v-for="(link, i) in activities.links" :key="i">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                  :class="link.active ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-200 hover:text-slate-900'"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>
