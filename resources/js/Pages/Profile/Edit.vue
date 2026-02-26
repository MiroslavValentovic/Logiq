<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
import { useProfileEdit } from './useProfileEdit';

const props = defineProps({ user: Object, projects: { type: Array, default: () => [] } });
const {
  route,
  profileForm,
  updateProfile,
  passwordForm,
  updatePassword,
  deleteForm,
  deleteAccount,
  deleteModalOpen,
  showDeleteModal,
  closeDeleteModal,
  profileSubmitting,
} = useProfileEdit(props.user);

const initials = computed(() => {
  const u = props.user;
  if (!u) return '';
  const f = (u.first_name || '').trim();
  const l = (u.last_name || '').trim();
  return ((f[0] || '') + (l[0] || '')).toUpperCase();
});

const avatarPreviewUrl = ref(null);

function onAvatarChange(e) {
  const f = e.target?.files?.[0];
  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value);
    avatarPreviewUrl.value = null;
  }
  if (f) {
    avatarPreviewUrl.value = URL.createObjectURL(f);
    profileForm.avatar = f;
  }
}

watch(() => props.user, () => {
  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value);
    avatarPreviewUrl.value = null;
  }
});

watch(
  () => [props.user?.is_admin, activeSection.value],
  ([isAdmin, section]) => {
    if (isAdmin && section === 'projects') activeSection.value = 'profil';
  },
  { immediate: true }
);

onUnmounted(() => {
  if (avatarPreviewUrl.value) URL.revokeObjectURL(avatarPreviewUrl.value);
});

const activeSection = ref('profil');
const sections = computed(() => {
  const list = [
    { id: 'profil', label: 'Profil' },
    { id: 'password', label: 'Heslo' },
    { id: 'account', label: 'Účet' },
  ];
  if (!props.user?.is_admin) {
    list.splice(2, 0, { id: 'projects', label: 'Projekty' });
  }
  return list;
});
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h1 class="page-title">Nastavenia</h1>
    </template>

    <div class="flex flex-col gap-8 lg:flex-row lg:gap-12">
      <!-- Sidebar (desktop) – rovnaký štýl ako admin -->
      <aside class="hidden shrink-0 lg:block lg:w-48">
        <nav class="sticky top-24 flex flex-col gap-0.5" aria-label="Sekcie nastavení">
          <button
            v-for="s in sections"
            :key="s.id"
            type="button"
            class="rounded-r-lg border-l-2 border-transparent py-2.5 pl-4 pr-3 text-left text-sm font-medium transition-colors"
            :class="activeSection === s.id
              ? 'border-primary-500 bg-primary-50 pl-[14px] text-primary-700'
              : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
            @click="activeSection = s.id"
          >
            {{ s.label }}
          </button>
        </nav>
      </aside>

      <main class="min-w-0 flex-1">
        <div class="mb-6 lg:hidden">
          <label for="settings-section" class="sr-only">Sekcia nastavení</label>
          <select
            id="settings-section"
            :value="activeSection"
            class="input-field w-full rounded-xl bg-white px-4 py-3 text-sm"
            @change="activeSection = $event.target.value"
          >
            <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.label }}</option>
          </select>
        </div>
        <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/80 shadow-sm sm:p-8">
        <!-- Sekcia: Profil -->
        <div v-show="activeSection === 'profil'" class="max-w-xl">
          <h2 class="text-lg font-bold text-slate-900">Osobné údaje</h2>
          <p class="mt-1 text-sm text-slate-600">{{ user?.is_admin ? 'Meno a e‑mail účtu.' : 'Meno, priezvisko, profilová fotka a e‑mail.' }}</p>
          <form @submit.prevent="updateProfile" class="mt-6 space-y-4">
            <div class="flex flex-wrap items-start gap-6">
              <div v-if="!user?.is_admin" class="flex flex-col items-center gap-2">
                <div class="flex h-24 w-24 overflow-hidden rounded-full border-2 border-slate-200 bg-slate-100">
                  <img v-if="avatarPreviewUrl" :src="avatarPreviewUrl" alt="Náhľad" class="h-full w-full object-cover" />
                  <img v-else-if="user?.avatar_url" :src="user.avatar_url" alt="Profilová fotka" class="h-full w-full object-cover" />
                  <span v-else class="flex h-full w-full items-center justify-center text-2xl font-semibold text-slate-500">{{ initials }}</span>
                </div>
                <label class="cursor-pointer text-sm font-medium text-primary-600 hover:text-primary-700">
                  <input type="file" accept="image/*" class="sr-only" @change="onAvatarChange" />
                  {{ profileForm.avatar ? 'Vybrať inú' : 'Nahrať fotku' }}
                </label>
                <p v-if="profileForm.errors.avatar" class="text-sm text-red-600">{{ profileForm.errors.avatar }}</p>
              </div>
              <div class="min-w-0 flex-1 space-y-4">
                <div>
                  <label for="first_name" class="block text-sm font-semibold text-slate-700">Meno</label>
                  <input id="first_name" v-model="profileForm.first_name" type="text" required autocomplete="given-name" class="input-field mt-1.5 border px-4 py-2.5" />
                  <p v-if="profileForm.errors.first_name" class="mt-1.5 text-sm text-red-600">{{ profileForm.errors.first_name }}</p>
                </div>
                <div v-if="!user?.is_admin">
                  <label for="last_name" class="block text-sm font-semibold text-slate-700">Priezvisko</label>
                  <input id="last_name" v-model="profileForm.last_name" type="text" required autocomplete="family-name" class="input-field mt-1.5 border px-4 py-2.5" />
                  <p v-if="profileForm.errors.last_name" class="mt-1.5 text-sm text-red-600">{{ profileForm.errors.last_name }}</p>
                </div>
              </div>
            </div>
            <div>
              <label for="email" class="block text-sm font-semibold text-slate-700">E‑mail</label>
              <input id="email" v-model="profileForm.email" type="email" required autocomplete="username" class="input-field mt-1.5 border px-4 py-2.5" />
              <p v-if="profileForm.errors.email" class="mt-1.5 text-sm text-red-600">{{ profileForm.errors.email }}</p>
            </div>
            <div class="flex items-center gap-4">
              <button type="submit" class="btn-primary" :disabled="profileSubmitting">Uložiť</button>
              <p v-if="$page.props.flash?.status === 'profile-updated'" class="text-sm text-primary-600">Uložené.</p>
            </div>
          </form>
        </div>

        <!-- Sekcia: Heslo -->
        <div v-show="activeSection === 'password'" class="max-w-xl">
          <h2 class="text-lg font-bold text-slate-900">Zmena hesla</h2>
          <p class="mt-1 text-sm text-slate-600">Zadajte nové heslo a potvrďte ho.</p>
          <form @submit.prevent="updatePassword" class="mt-6 space-y-4">
            <div>
              <label for="password" class="block text-sm font-semibold text-slate-700">Nové heslo</label>
              <input id="password" v-model="passwordForm.password" type="password" autocomplete="new-password" class="input-field mt-1.5 border px-4 py-2.5" />
              <p v-if="passwordForm.errors.password" class="mt-1.5 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Potvrdiť nové heslo</label>
              <input id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" class="input-field mt-1.5 border px-4 py-2.5" />
              <p v-if="passwordForm.errors.password_confirmation" class="mt-1.5 text-sm text-red-600">{{ passwordForm.errors.password_confirmation }}</p>
            </div>
            <div class="flex items-center gap-4">
              <button type="submit" class="btn-primary" :disabled="passwordForm.processing">Uložiť heslo</button>
              <p v-if="$page.props.flash?.status === 'password-updated'" class="text-sm text-primary-600">Uložené.</p>
            </div>
          </form>
        </div>

        <!-- Sekcia: Projekty (iba pre ne-adminov) -->
        <div v-show="activeSection === 'projects' && !user?.is_admin">
          <h2 class="text-lg font-bold text-slate-900">Moje projekty</h2>
          <p class="mt-1 text-sm text-slate-600">Projekty, pod ktoré môžete zapisovať work logy.</p>
          <div class="mt-6">
            <p v-if="!projects?.length" class="text-slate-500">Nemáte priradené žiadne projekty. O projekty vás môže požiadať administrátor.</p>
            <div v-else class="overflow-hidden rounded-xl border border-slate-200">
              <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/80">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Názov</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Zákazník</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Stav</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                  <tr v-for="p in projects" :key="p.id" class="transition hover:bg-slate-50/50">
                    <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ p.name }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ p.client_name || '—' }}</td>
                    <td class="px-4 py-3">
                      <span :class="p.is_active ? 'bg-primary-100 text-primary-800' : 'bg-slate-100 text-slate-600'" class="rounded-full px-2.5 py-1 text-xs font-medium">
                        {{ p.is_active ? 'Aktívny' : 'Neaktívny' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-if="user?.is_admin" class="mt-4 text-sm text-slate-500">
              Ako administrátor môžete <Link :href="route('projects.index')" class="font-medium text-primary-600 hover:text-primary-700">spravovať projekty</Link> (všetky projekty a pridávanie nových).
            </p>
          </div>
        </div>

        <!-- Sekcia: Účet -->
        <div v-show="activeSection === 'account'" class="max-w-xl">
          <h2 class="text-lg font-bold text-slate-900">Zmazať účet</h2>
          <p class="mt-1 text-sm text-slate-600">Po zmazaní účtu budú všetky vaše údaje natrvalo odstránené.</p>
          <button type="button" class="mt-4 rounded-lg border-2 border-red-500 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50" @click="showDeleteModal">Zmazať účet</button>
        </div>
        </div>
      </main>
    </div>

    <!-- Delete modal -->
    <Teleport to="body">
      <div v-if="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeDeleteModal">
        <div class="mx-4 w-full max-w-md rounded-2xl bg-white p-8 shadow-card" @click.stop>
          <h3 class="text-lg font-bold text-slate-900">Naozaj chcete zmazať svoj účet?</h3>
          <p class="mt-1 text-sm text-slate-600">Zadajte heslo na potvrdenie.</p>
          <form @submit.prevent="deleteAccount" class="mt-6 space-y-4">
            <div>
              <label for="delete_password" class="sr-only">Heslo</label>
              <input id="delete_password" v-model="deleteForm.password" type="password" placeholder="Heslo" class="input-field mt-1.5 w-full border px-4 py-2.5" />
              <p v-if="deleteForm.errors.password" class="mt-1.5 text-sm text-red-600">{{ deleteForm.errors.password }}</p>
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" class="btn-secondary" @click="closeDeleteModal">Zrušiť</button>
              <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50" :disabled="deleteForm.processing">Zmazať účet</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>
