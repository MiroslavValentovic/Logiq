<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import UserSelectSearch from '@/Components/UserSelectSearch.vue';
import { useReportsShow } from './useReportsShow';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin ?? false);

const props = defineProps({
  report: Object,
  workLogs: Array,
  totalMinutes: Number,
  monthStart: String,
  year: Number,
  month: Number,
  usersForAdmin: { type: Array, default: () => [] },
  selectedUserId: { type: Number, default: null },
});

const selectedUserId = ref(props.selectedUserId ?? props.usersForAdmin?.[0]?.id ?? null);
watch(() => props.selectedUserId, (id) => { selectedUserId.value = id ?? props.usersForAdmin?.[0]?.id ?? null; });
watch(selectedUserId, (userId) => {
  if (userId != null && userId !== props.selectedUserId && props.monthStart) {
    router.get(route('reports.show', props.monthStart), { user_id: userId }, { preserveScroll: true, replace: true });
  }
});

const { route } = useReportsShow();

const unlockModalOpen = ref(false);
function showUnlockModal() { unlockModalOpen.value = true; }
function closeUnlockModal() { unlockModalOpen.value = false; }
function confirmUnlock() {
  const data = { year: props.year, month: props.month };
  if (props.selectedUserId != null) data.user_id = props.selectedUserId;
  router.post(route('reports.unlock'), data);
  closeUnlockModal();
}

const indexHref = props.selectedUserId != null
  ? `${route('reports.index')}?user_id=${props.selectedUserId}`
  : route('reports.index');
const downloadPdfHref = props.monthStart
  ? (props.selectedUserId != null ? `${route('reports.download', props.monthStart)}?user_id=${props.selectedUserId}` : route('reports.download', props.monthStart))
  : '#';

const workLogsByDay = computed(() => {
  const map = {};
  (props.workLogs || []).forEach((log) => {
    const key = (log.work_date || '').toString().slice(0, 10);
    if (!key) return;
    if (!map[key]) map[key] = [];
    map[key].push(log);
  });
  return Object.entries(map).sort((a, b) => a[0].localeCompare(b[0]));
});

function formatDate(str) {
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
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <h1 class="page-title shrink-0">Report — {{ formatDate(monthStart) }}</h1>
          <div v-if="usersForAdmin?.length" class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2">
            <label class="shrink-0 text-sm font-medium text-slate-600">Používateľ:</label>
            <UserSelectSearch
              v-model="selectedUserId"
              :users="usersForAdmin"
              placeholder="Vyberte používateľa"
            />
          </div>
        </div>
        <div class="flex flex-wrap gap-3">
          <Link :href="indexHref" class="btn-secondary">Späť na reporty</Link>
          <template v-if="report?.status === 'locked'">
            <a :href="downloadPdfHref" class="cursor-pointer rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 inline-block">Stiahnuť PDF</a>
            <button v-if="isAdmin" type="button" class="btn-secondary border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100" @click="showUnlockModal">Odomknúť</button>
          </template>
        </div>
      </div>
    </template>

    <div
      class="rounded-2xl overflow-hidden shadow-sm transition-colors"
      :class="report?.status === 'locked' ? 'border-2 border-amber-300 bg-amber-50/20' : 'border border-slate-200/80 bg-white'"
    >
      <div v-if="report?.status === 'locked' && isAdmin" class="border-b border-amber-200 bg-amber-100/80 px-6 py-3 text-sm font-medium text-amber-900">
        Výkaz je uzamknutý ({{ formatDate(report.locked_at) }}). Používateľ nemôže meniť záznamy; môžete ho odomknúť tlačidlom v hlavičke.
      </div>
      <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-slate-700">
          Spolu: <strong class="text-primary-700">{{ formatHours(totalMinutes) }}</strong>
          <span v-if="report?.status === 'locked'" class="ml-2 text-amber-600 text-sm">— Zamknuté {{ formatDate(report.locked_at) }}</span>
        </p>
      </div>
      <div class="p-6">
        <p v-if="!workLogs?.length" class="text-slate-500">Pre tento mesiac nie sú žiadne záznamy.</p>
        <div v-else class="rounded-xl border border-slate-200 overflow-hidden">
          <table class="min-w-full">
            <thead>
              <tr class="bg-primary-600">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Dátum</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Projekt</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Hodiny</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">Poznámka</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <template v-for="[dateStr, dayLogs] in workLogsByDay" :key="dateStr">
                <tr class="bg-primary-50">
                  <td colspan="4" class="px-5 py-2.5 text-sm font-semibold text-primary-800">
                    {{ formatDayLabel(dateStr) }}
                  </td>
                </tr>
                <tr
                  v-for="log in dayLogs"
                  :key="log.id"
                  class="divide-x divide-slate-100 transition hover:bg-slate-50/70"
                >
                  <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ formatDate(log.work_date) }}</td>
                  <td class="px-5 py-3 text-sm text-slate-900">{{ log.project?.name }}</td>
                  <td class="px-5 py-3 text-sm text-slate-900">{{ formatHours(log.minutes) }}</td>
                  <td class="max-w-md break-words px-5 py-3 text-sm text-slate-600 whitespace-normal">{{ log.note || '—' }}</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Odomknúť report -->
    <Teleport to="body">
      <div v-if="unlockModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeUnlockModal">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl" @click.stop>
          <h3 class="text-lg font-bold text-slate-900">Odomknúť report</h3>
          <p class="mt-2 text-sm text-slate-600">
            Záznamy práce za tento mesiac budú opäť editovateľné. PDF zostane uložené.
          </p>
          <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button type="button" class="btn-secondary" @click="closeUnlockModal">Zrušiť</button>
            <button type="button" class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500" @click="confirmUnlock">Odomknúť</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>
