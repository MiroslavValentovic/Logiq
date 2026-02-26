<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import { useWorkLogsIndex } from './useWorkLogsIndex';

function formatReportDate(str) {
  if (!str) return '—';
  const d = new Date(str);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}.${month}.${year}`;
}

function formatDayLabel(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr + 'T12:00:00');
  return d.toLocaleDateString('sk-SK', { weekday: 'long', day: 'numeric', month: 'numeric', year: 'numeric' });
}

function formatHours(minutes) {
  if (minutes == null) return '—';
  const h = minutes / 60;
  return (Number.isInteger(h) ? h : h.toFixed(1).replace('.', ',')) + ' h';
}

const props = defineProps({
  workLogs: Object,
  projects: Array,
  year: Number,
  month: Number,
  view: String,
  usersForAdmin: { type: Array, default: () => [] },
  isCurrentMonthLocked: { type: Boolean, default: false },
  hasPendingUnlockRequest: { type: Boolean, default: false },
});

const unlockRequestSubmitting = ref(false);
function submitUnlockRequest() {
  unlockRequestSubmitting.value = true;
  router.post(route('report-unlock-request.store'), { year: props.year, month: props.month }, {
    preserveScroll: true,
    onFinish: () => { unlockRequestSubmitting.value = false; },
  });
}

const workLogsByDay = computed(() => {
  const list = props.workLogs?.data ?? [];
  const map = {};
  list.forEach((log) => {
    const key = (log.work_date || '').toString().slice(0, 10);
    if (!key) return;
    if (!map[key]) map[key] = [];
    map[key].push(log);
  });
  return Object.entries(map).sort((a, b) => a[0].localeCompare(b[0]));
});

const {
  route,
  WEEKDAYS,
  currentView,
  filterYear,
  filterMonth,
  calendarWeeks,
  calendarLoading,
  monthLabel,
  year,
  month,
  currentYear,
  isMinMonth,
  getEventsForDay,
  goPrev,
  goNext,
  goToday,
  go,
  createUrl,
  editUrl,
  onDayClick,
  onDayDblclick,
  showDeleteModal,
  deleteModalOpen,
  closeDeleteModal,
  confirmDelete,
  submitFilter,
} = useWorkLogsIndex(props);
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">Záznamy práce</h1>
        <div class="flex flex-wrap items-center gap-3">
          <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50/80 p-0.5">
            <button
              type="button"
              :class="currentView === 'calendar' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
              class="rounded-lg px-3 py-2 text-sm font-medium transition"
              @click="go('calendar', year, month)"
            >
              Kalendár
            </button>
            <button
              type="button"
              :class="currentView === 'list' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
              class="rounded-lg px-3 py-2 text-sm font-medium transition"
              @click="go('list', year, month)"
            >
              Zoznam
            </button>
          </div>
          <Link v-if="!isCurrentMonthLocked" :href="route('work-logs.create')" class="btn-primary">Pridať záznam</Link>
        </div>
      </div>
    </template>

    <!-- Custom calendar view -->
    <div v-if="currentView === 'calendar'" class="space-y-4">
      <!-- Nad kalendárom: text a tlačidlo pri zamknutom mesiaci -->
      <div
        v-if="isCurrentMonthLocked"
        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-amber-300 bg-amber-50 px-4 py-4 sm:px-6"
      >
        <p class="text-base font-medium text-amber-900">
          Výkaz pre tento mesiac je uzamknutý. Nemôžete pridávať ani meniť záznamy práce.
        </p>
        <div class="flex shrink-0 flex-wrap items-center gap-3">
          <p v-if="hasPendingUnlockRequest" class="text-sm font-medium text-primary-600">
            Požiadavka na odomknutie bola odoslaná administrátorovi. O vybavení vás budeme informovať.
          </p>
          <button
            v-else
            type="button"
            class="btn-primary"
            :disabled="unlockRequestSubmitting"
            @click="submitUnlockRequest"
          >
            {{ unlockRequestSubmitting ? 'Odosielam…' : 'Požiadať administrátora o odomknutie' }}
          </button>
        </div>
        <p class="mt-2 w-full text-xs text-amber-800/90">
          Požiadavku môžete odoslať aj zo stránky <Link :href="route('reports.index', { year, month })" class="font-medium underline hover:no-underline">Mesačné reporty</Link>.
        </p>
      </div>

      <div
        class="card overflow-hidden p-0 transition-colors"
        :class="isCurrentMonthLocked ? 'ring-2 ring-amber-400 border-amber-300 bg-amber-50/20' : ''"
      >
        <!-- Hlavička kalendára s navigáciou -->
        <div class="border-b border-slate-200 bg-slate-50/50 px-4 py-3 sm:px-6">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-slate-900">{{ monthLabel }}</h2>
            <div class="flex items-center gap-2">
            <button
              type="button"
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
              aria-label="Predchádzajúci mesiac"
              :disabled="isMinMonth"
              @click="goPrev"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
              </button>
              <button
                type="button"
                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                @click="goToday"
              >
                Dnes
              </button>
              <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-50 hover:border-slate-400"
                aria-label="Ďalší mesiac"
                @click="goNext"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
              </button>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full min-w-[400px] table-fixed border-collapse">
          <thead>
            <tr>
              <th
                v-for="(wd, wdi) in WEEKDAYS"
                :key="wd"
                class="border-b border-slate-200 py-2.5 text-center text-xs font-semibold uppercase tracking-wider"
                :class="wdi >= 5 ? 'bg-slate-100 text-slate-500' : 'bg-slate-50/80 text-slate-500'"
              >
                {{ wd }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(week, wi) in calendarWeeks" :key="wi" class="last:border-b-0">
              <td
                v-for="(day, di) in week"
                :key="day.iso"
                class="align-top border-b border-r p-0 last:border-r-0"
                :class="[
                  di >= 5 && day.isCurrentMonth
                    ? 'border-slate-200 bg-slate-100'
                    : 'border-slate-100',
                  di < 5 && (day.isCurrentMonth ? 'bg-white' : 'bg-slate-50/50'),
                  di >= 5 && !day.isCurrentMonth && 'bg-slate-50/50',
                  day.isToday && 'ring-inset ring-2 ring-primary-500',
                ]"
              >
                <div
                  class="flex min-h-[100px] flex-col p-1.5 sm:min-h-[110px] sm:p-2"
                  @click="onDayClick(day)"
                  @dblclick="!isCurrentMonthLocked && onDayDblclick(day)"
                >
                  <span
                    class="mb-1 flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center self-end rounded-lg text-sm font-medium transition sm:h-8 sm:w-8 select-none"
                    :class="
                      day.isToday
                        ? 'bg-primary-600 text-white hover:bg-primary-700'
                        : day.isCurrentMonth
                          ? 'text-slate-700 hover:bg-slate-100'
                          : 'text-slate-400 hover:bg-slate-100'
                    "
                  >
                    {{ day.date }}
                  </span>
                  <div v-if="calendarLoading" class="flex flex-1 items-center justify-center text-xs text-slate-400">
                    …
                  </div>
                  <div v-else class="flex flex-1 flex-col gap-1 overflow-hidden">
                    <template v-for="ev in getEventsForDay(day.iso)" :key="ev.id">
                      <a
                        v-if="!isCurrentMonthLocked"
                        :href="editUrl(ev)"
                        class="block truncate rounded-lg bg-primary-600 px-2 py-1 text-left text-xs font-medium text-white transition hover:bg-primary-700"
                        :title="ev.title"
                      >
                        {{ ev.title }}
                      </a>
                      <span
                        v-else
                        class="block truncate rounded-lg bg-primary-600/80 px-2 py-1 text-left text-xs font-medium text-white cursor-default"
                        :title="ev.title"
                      >
                        {{ ev.title }}
                      </span>
                    </template>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
        <div class="border-t border-slate-200 bg-slate-50/50 px-4 py-3 sm:px-6">
          <p class="text-xs font-medium text-slate-500">Ovládanie:</p>
          <p v-if="isCurrentMonthLocked" class="mt-0.5 text-sm text-slate-600">
            Kalendár je len na prehliadanie — výkaz za tento mesiac je uzamknutý.
          </p>
          <p v-else class="mt-0.5 text-sm text-slate-600">
            Klik na deň — výber dátumu; dvojklik na deň — pridať záznam; klik na udalosť — upraviť záznam.
          </p>
        </div>
      </div>
    </div>

    <!-- List view -->
    <template v-else>
      <div v-if="isCurrentMonthLocked" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <p class="font-medium">Výkaz pre tento mesiac je uzamknutý.</p>
        <p class="mt-1">Nemôžete pridávať ani meniť záznamy. Ak potrebujete úpravu, požiadajte administrátora o odomknutie (prepnite na kalendár).</p>
      </div>
      <div class="mb-6 flex flex-wrap items-center gap-3">
        <form class="flex gap-2 rounded-xl border border-slate-200 bg-white p-2 shadow-sm" @submit.prevent="submitFilter">
          <input v-model.number="filterYear" type="number" :min="currentYear" max="2030" class="input-field w-24 border px-3 py-2 text-sm" placeholder="Rok" />
          <input v-model.number="filterMonth" type="number" min="1" max="12" class="input-field w-20 border px-3 py-2 text-sm" placeholder="Mesiac" />
          <button type="submit" class="btn-secondary rounded-lg py-2">Filtrovať</button>
        </form>
      </div>
      <div class="rounded-2xl border border-slate-200/80 overflow-hidden bg-white shadow-sm">
        <div class="p-6">
          <p v-if="!workLogs.data?.length" class="text-slate-500">Pre tento mesiac nie sú žiadne záznamy.</p>
          <Link v-if="!workLogs.data?.length && !isCurrentMonthLocked" :href="route('work-logs.create')" class="mt-2 inline-block font-medium text-primary-600 hover:text-primary-700">Pridať prvý záznam</Link>
          <div v-else class="rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full">
              <thead>
                <tr class="bg-primary-600">
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Dátum</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Projekt</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Hodiny</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Poznámka</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white">Akcie</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 bg-white">
                <template v-for="[dateStr, dayLogs] in workLogsByDay" :key="dateStr">
                  <tr class="bg-primary-50">
                    <td colspan="5" class="px-5 py-2.5 text-sm font-semibold text-primary-800">
                      {{ formatDayLabel(dateStr) }}
                    </td>
                  </tr>
                  <tr
                    v-for="log in dayLogs"
                    :key="log.id"
                    class="transition hover:bg-slate-50/70"
                  >
                    <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ formatReportDate(log.work_date) }}</td>
                    <td class="px-5 py-3 text-sm text-slate-900">{{ log.project?.name }}</td>
                    <td class="px-5 py-3 text-sm text-slate-900">{{ formatHours(log.minutes) }}</td>
                    <td class="max-w-md break-words px-5 py-3 text-sm text-slate-600 whitespace-normal">{{ log.note || '—' }}</td>
                    <td class="px-5 py-3 text-right text-sm">
                      <div v-if="!isCurrentMonthLocked" class="flex flex-wrap items-center justify-end gap-1">
                        <Link :href="route('work-logs.edit', log.id)" class="btn-ghost rounded-lg">Upraviť</Link>
                        <button type="button" class="btn-danger-ghost rounded-lg" @click="showDeleteModal(log.id)">Zmazať</button>
                      </div>
                      <span v-else class="text-slate-400 text-xs">—</span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          <div v-if="workLogs.data?.length" class="mt-6 flex flex-wrap justify-end gap-1">
            <Link
              v-for="(link, i) in workLogs.links"
              :key="i"
              :href="link.url"
              class="pagination-btn rounded-lg"
              :class="link.active ? 'pagination-btn-active' : 'pagination-btn-inactive'"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </template>

    <!-- Modal: Zmazať záznam -->
    <Teleport to="body">
      <div v-if="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeDeleteModal">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="delete-worklog-modal-title">
          <h2 id="delete-worklog-modal-title" class="text-lg font-bold text-slate-900">Zmazať záznam</h2>
          <p class="mt-2 text-slate-600">Naozaj chcete zmazať tento záznam práce?</p>
          <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="btn-secondary cursor-pointer" @click="closeDeleteModal">Zrušiť</button>
            <button type="button" class="cursor-pointer rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500" @click="confirmDelete">Zmazať</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>
