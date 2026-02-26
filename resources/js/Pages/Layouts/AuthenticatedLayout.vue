<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.is_admin);
const route = (name, ...params) => window.route(name, ...params);

const visibleSuccess = ref('');
const visibleError = ref('');
const visibleInfo = ref('');
const DISMISS_MS = 5000;
let successTimer = null;
let errorTimer = null;
let infoTimer = null;

function scheduleDismiss() {
  const flash = page.props.flash || {};
  if (flash.success) {
    visibleSuccess.value = flash.success;
    clearTimeout(successTimer);
    successTimer = setTimeout(() => { visibleSuccess.value = ''; }, DISMISS_MS);
  } else visibleSuccess.value = '';
  if (flash.error) {
    visibleError.value = flash.error;
    clearTimeout(errorTimer);
    errorTimer = setTimeout(() => { visibleError.value = ''; }, DISMISS_MS);
  } else visibleError.value = '';
  if (flash.info) {
    visibleInfo.value = flash.info;
    clearTimeout(infoTimer);
    infoTimer = setTimeout(() => { visibleInfo.value = ''; }, DISMISS_MS);
  } else visibleInfo.value = '';
}

watch(() => page.props.flash, scheduleDismiss, { immediate: true, deep: true });

const navLinks = computed(() => [
  { name: 'Prehľad', href: route('dashboard'), match: '/dashboard' },
  { name: 'Záznamy práce', href: route('work-logs.index'), match: '/work-logs' },
  { name: 'Reporty', href: route('reports.index'), match: '/reports' },
]);

const isActive = (match) => {
  const url = page.url;
  if (match === '/dashboard') return url === '/dashboard';
  return url.startsWith(match);
};

const userInitials = computed(() => {
  const u = user.value;
  if (!u) return '';
  const f = (u.first_name || '').trim();
  const l = (u.last_name || '').trim();
  return ((f[0] || '') + (l[0] || '')).toUpperCase();
});

const pendingUnlockRequestsCount = computed(() => Number(page.props.pendingUnlockRequestsCount) || 0);
const unreadNotificationsCount = computed(() => Number(page.props.unreadNotificationsCount) || 0);

const profileMenuOpen = ref(false);
const profileMenuRef = ref(null);

function onDocumentClick(e) {
  if (profileMenuRef.value && !profileMenuRef.value.contains(e.target)) profileMenuOpen.value = false;
}

function onProfileTriggerKeydown(e) {
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault();
    profileMenuOpen.value = !profileMenuOpen.value;
  }
}

onMounted(() => document.addEventListener('click', onDocumentClick));
onUnmounted(() => document.removeEventListener('click', onDocumentClick));
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Nav -->
    <nav class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur-sm">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">
          <div class="flex items-center gap-10">
            <Link
              :href="route('dashboard')"
              class="flex shrink-0 items-center gap-2 text-lg font-bold tracking-tight text-slate-900"
            >
              <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">L</span>
              Logiq
            </Link>
            <div class="hidden gap-1 sm:flex">
              <Link
                v-for="link in navLinks"
                :key="link.name"
                :href="link.href"
                class="rounded-lg px-3 py-2 text-sm font-medium transition"
                :class="isActive(link.match)
                  ? 'bg-primary-50 text-primary-700'
                  : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
              >
                {{ link.name }}
              </Link>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <!-- Admin: požiadavky na odomknutie -->
            <Link
              v-if="isAdmin"
              :href="route('admin.unlock-requests.index')"
              class="relative flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
              :aria-label="pendingUnlockRequestsCount > 0 ? `Požiadavky na odomknutie: ${pendingUnlockRequestsCount}` : 'Požiadavky'"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="pendingUnlockRequestsCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-xs font-bold text-white"
              >
                {{ pendingUnlockRequestsCount > 99 ? '99+' : pendingUnlockRequestsCount }}
              </span>
            </Link>
            <!-- Používateľ: notifikácie (schválenie/zamietnutie požiadavky) -->
            <Link
              v-if="!isAdmin"
              :href="route('notifications.index')"
              class="relative flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
              :aria-label="unreadNotificationsCount > 0 ? `Notifikácie: ${unreadNotificationsCount}` : 'Notifikácie'"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="unreadNotificationsCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-xs font-bold text-white"
              >
                {{ unreadNotificationsCount > 99 ? '99+' : unreadNotificationsCount }}
              </span>
            </Link>
            <Link
              v-if="isAdmin"
              :href="route('admin.dashboard')"
              class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
            >
              Nastavenia
            </Link>
            <div ref="profileMenuRef" class="profile-menu-wrapper relative">
              <div
                role="button"
                tabindex="0"
                class="profile-trigger-btn flex h-10 w-10 shrink-0 cursor-pointer select-none items-center justify-center overflow-hidden rounded-full transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                :class="isAdmin
                  ? 'border-2 border-amber-400 bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-[0_0_12px_rgba(251,191,36,0.6)]'
                  : 'border border-slate-200 bg-slate-100 text-slate-700 hover:bg-slate-200'"
                aria-haspopup="true"
                :aria-expanded="profileMenuOpen"
                @click="profileMenuOpen = !profileMenuOpen"
                @keydown="onProfileTriggerKeydown"
              >
                <img v-if="!isAdmin && user?.avatar_url" :src="user.avatar_url" alt="" class="h-full w-full object-cover" />
                <span v-else class="profile-trigger-label text-xs font-bold">{{ isAdmin ? 'ADM' : userInitials }}</span>
              </div>
              <div
                v-show="profileMenuOpen"
                class="absolute right-0 top-full z-50 mt-2 w-52 origin-top-right rounded-xl border border-slate-200 bg-white p-2 shadow-lg ring-1 ring-black/5 transition"
              >
                <Link
                  :href="route('profile.edit')"
                  class="flex cursor-pointer select-none items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 caret-transparent transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-800"
                  @click="profileMenuOpen = false"
                >
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-medium text-slate-600">
                    <img v-if="!isAdmin && user?.avatar_url" :src="user.avatar_url" alt="" class="h-full w-full rounded-full object-cover" />
                    <span v-else>{{ isAdmin ? 'ADM' : userInitials }}</span>
                  </span>
                  Zobraziť profil
                </Link>
                <Link
                  :href="route('logout')"
                  method="post"
                  as="button"
                  class="mt-2 flex w-full cursor-pointer select-none items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-left text-sm text-slate-700 caret-transparent transition hover:border-red-300 hover:bg-red-50 hover:text-red-700"
                  @click="profileMenuOpen = false"
                >
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                  </span>
                  Odhlásiť sa
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page header -->
    <header v-if="$slots.header" class="border-b border-slate-200/80 bg-white">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Obsah -->
    <main class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div v-if="visibleSuccess" class="mb-6 rounded-xl bg-primary-50 px-4 py-3 text-sm font-medium text-primary-800">
          {{ visibleSuccess }}
        </div>
        <div v-if="visibleError" class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
          {{ visibleError }}
        </div>
        <div v-if="visibleInfo" class="mb-6 rounded-xl bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">
          {{ visibleInfo }}
        </div>
        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Skrytie blikajúcej textovej kurzory (caret) pri ikone profilu a v dropdown */
.profile-menu-wrapper :deep(button),
.profile-menu-wrapper :deep([type="submit"]) {
  caret-color: transparent;
}
.profile-menu-wrapper :deep(.profile-trigger-btn),
.profile-menu-wrapper :deep(.profile-trigger-btn *),
.profile-menu-wrapper :deep(.profile-trigger-label) {
  caret-color: transparent !important;
}
</style>
