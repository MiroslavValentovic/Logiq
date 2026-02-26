<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import UserSelectSearch from '@/Components/UserSelectSearch.vue';
import { useReportsIndex } from './useReportsIndex';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin ?? false);

const props = defineProps({
  report: { type: Object, default: null },
  workLogs: { type: Array, default: () => [] },
  totalMinutes: { type: Number, default: 0 },
  year: { type: Number, default: () => new Date().getFullYear() },
  month: { type: Number, default: () => new Date().getMonth() + 1 },
  monthStart: { type: String, default: '' },
  usersForAdmin: { type: Array, default: () => [] },
  selectedUserId: { type: Number, default: null },
  unlockRequestPending: { type: Boolean, default: false },
});

const selectedUserId = ref(props.selectedUserId ?? props.usersForAdmin?.[0]?.id ?? null);
watch(() => props.selectedUserId, (id) => { selectedUserId.value = id ?? props.usersForAdmin?.[0]?.id ?? null; });
watch(selectedUserId, (userId) => {
  if (userId != null && userId !== props.selectedUserId) {
    router.get(route('reports.index'), { user_id: userId, year: props.year, month: props.month }, { preserveScroll: true, replace: true });
  }
});

const { route, lock, unlock, isMinMonth, goPrev, goNext, goToday } = useReportsIndex(props);

function navUserId() {
  return selectedUserId.value ?? undefined;
}

const unlockRequestSubmitting = ref(false);
function submitUnlockRequest() {
  unlockRequestSubmitting.value = true;
  router.post(route('report-unlock-request.store'), { year: props.year, month: props.month }, {
    preserveScroll: true,
    onFinish: () => { unlockRequestSubmitting.value = false; },
  });
}

const lockModalOpen = ref(false);
const unlockModalOpen = ref(false);
function showLockModal() { lockModalOpen.value = true; }
function closeLockModal() { lockModalOpen.value = false; }
function confirmLock() { lock(); closeLockModal(); }
function showUnlockModal() { unlockModalOpen.value = true; }
function closeUnlockModal() { unlockModalOpen.value = false; }
function confirmUnlock() { unlock(); closeUnlockModal(); }

const totalMinutes = computed(() => props.totalMinutes ?? 0);
function formatHours(minutes) {
  if (minutes == null) return '—';
  const h = minutes / 60;
  return (Number.isInteger(h) ? h : h.toFixed(1).replace('.', ',')) + ' h';
}
const monthLabel = computed(() => {
  if (!props.monthStart) return '—';
  const d = new Date(props.monthStart + 'T12:00:00');
  const s = d.toLocaleDateString('sk-SK', { month: 'long', year: 'numeric' });
  return s.charAt(0).toUpperCase() + s.slice(1);
});

const showReportHref = computed(() => {
  if (!props.monthStart) return '#';
  const base = route('reports.show', props.monthStart);
  return props.selectedUserId != null ? `${base}?user_id=${props.selectedUserId}` : base;
});
const downloadPdfHref = computed(() => {
  if (!props.monthStart) return '#';
  const base = route('reports.download', props.monthStart);
  return props.selectedUserId != null ? `${base}?user_id=${props.selectedUserId}` : base;
});
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <h1 class="page-title shrink-0">Mesačné reporty</h1>
          <div v-if="usersForAdmin?.length" class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2">
            <label class="shrink-0 text-sm font-medium text-slate-600">Používateľ:</label>
            <UserSelectSearch
              v-model="selectedUserId"
              :users="usersForAdmin"
              placeholder="Vyberte používateľa"
            />
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2">
          <span class="text-lg font-bold text-slate-900">{{ monthLabel }}</span>
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white"
              aria-label="Predchádzajúci mesiac"
              :disabled="isMinMonth"
              @click="goPrev(navUserId())"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button
              type="button"
              class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              @click="goToday(navUserId())"
            >
              Dnes
            </button>
            <button
              type="button"
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
              aria-label="Ďalší mesiac"
              @click="goNext(navUserId())"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
          </div>
        </div>
      </div>
    </template>

    <div
      class="rounded-2xl border-2 p-8 shadow-sm transition-colors"
      :class="report?.status === 'locked'
        ? 'border-amber-300 bg-amber-50/30'
        : 'border-slate-200/80 bg-white'"
    >
      <div v-if="report?.status === 'locked' && isAdmin" class="mb-4 rounded-xl border border-amber-200 bg-amber-100/80 px-4 py-3 text-sm font-medium text-amber-900">
        Výkaz tohto používateľa je uzamknutý. Používateľ nemôže meniť záznamy; môžete ho odomknúť tlačidlom nižšie alebo v Požiadavkách.
      </div>
      <div v-else-if="report?.status === 'locked' && !isAdmin" class="mb-4 rounded-xl border border-amber-200 bg-amber-100/80 px-4 py-3 text-sm">
        <p class="font-medium text-amber-900">
          Výkaz je uzamknutý. Ak potrebujete upraviť záznamy, môžete požiadať administrátora o odomknutie.
        </p>
        <p v-if="unlockRequestPending" class="mt-2 text-primary-700">
          Požiadavka na odomknutie bola odoslaná administrátorovi. O vybavení vás budeme informovať (notifikácie).
        </p>
        <button
          v-else
          type="button"
          class="mt-3 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 disabled:opacity-70"
          :disabled="unlockRequestSubmitting"
          @click="submitUnlockRequest"
        >
          {{ unlockRequestSubmitting ? 'Odosielam…' : 'Požiadať administrátora o odomknutie' }}
        </button>
        <p class="mt-3 text-xs text-amber-800/90">
          Túto požiadavku môžete odoslať aj na stránke <Link :href="route('work-logs.index', { year: props.year, month: props.month })" class="font-medium underline hover:no-underline">Svet práce (kalendár)</Link>.
        </p>
      </div>
      <div class="flex flex-wrap items-start gap-6">
        <div class="rounded-xl border border-slate-200 bg-slate-50/50 px-5 py-4 min-w-[200px]">
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mesiac</p>
          <p class="mt-1 text-lg font-bold text-slate-900">{{ monthLabel }}</p>
          <p class="mt-2">
            <span v-if="report?.status === 'locked'" class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Zamknuté</span>
            <span v-else class="inline-flex rounded-full bg-primary-100 px-2.5 py-1 text-xs font-medium text-primary-700">Otvorený</span>
          </p>
        </div>
        <div class="rounded-xl border border-primary-200 bg-primary-50/50 px-5 py-4 min-w-[140px]">
          <p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Spolu hodiny</p>
          <p class="mt-1 text-2xl font-bold text-primary-700">{{ formatHours(totalMinutes) }}</p>
        </div>
      </div>
      <div v-if="monthStart" class="mt-8 flex flex-wrap gap-3">
        <Link :href="showReportHref" class="btn-secondary rounded-xl">Náhľad reportu</Link>
        <template v-if="!report || report.status !== 'locked'">
          <button type="button" class="cursor-pointer rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500" @click="showLockModal">Zamknúť výkaz a vygenerovať PDF</button>
        </template>
        <template v-else>
          <a :href="downloadPdfHref" class="inline-flex items-center rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">Stiahnuť PDF</a>
          <button v-if="isAdmin" type="button" class="btn-secondary rounded-xl border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100" @click="showUnlockModal">Odomknúť</button>
        </template>
      </div>
    </div>

    <!-- Modal: Zamknúť výkaz a vygenerovať PDF -->
    <Teleport to="body">
      <div v-if="lockModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeLockModal">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl" @click.stop>
          <h3 class="text-lg font-bold text-slate-900">Zamknúť výkaz a vygenerovať PDF</h3>
          <p class="mt-2 text-sm text-slate-600">
            Po zamknutí už nebudete môcť meniť záznamy práce za tento mesiac. Vygeneruje sa PDF výkaz na stiahnutie.
          </p>
          <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button type="button" class="btn-secondary cursor-pointer" @click="closeLockModal">Zrušiť</button>
            <button type="button" class="cursor-pointer rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500" @click="confirmLock">Zamknúť výkaz a vygenerovať PDF</button>
          </div>
        </div>
      </div>
    </Teleport>

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
