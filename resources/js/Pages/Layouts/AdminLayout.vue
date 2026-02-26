<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';

const route = (name, ...params) => window.route(name, ...params);
const page = usePage();
const currentUrl = computed(() => page.url);

function goToAdminSection(url) {
  if (url) router.visit(url);
}

const navItems = [
  { label: 'Prehľad', href: route('admin.dashboard'), match: (url) => url === '/admin' || url === '/admin/' },
  { label: 'Používatelia', href: route('admin.users.index'), match: (url) => url.startsWith('/admin/users') },
  { label: 'Projekty', href: route('admin.projects.index'), match: (url) => url.startsWith('/admin/projects') },
  { label: 'Požiadavky', href: route('admin.unlock-requests.index'), match: (url) => url.startsWith('/admin/unlock-requests') },
  { label: 'Logy', href: route('admin.logs.index'), match: (url) => url.startsWith('/admin/logs') },
];

const isActive = (item) => item.match(currentUrl.value);
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <slot name="header" />
    </template>

    <div class="flex flex-col gap-8 lg:flex-row lg:gap-12">
      <!-- Sidebar (desktop) – minimalistický zoznam -->
      <aside class="hidden shrink-0 lg:block lg:w-48">
        <nav
          class="sticky top-24 flex flex-col gap-0.5"
          aria-label="Admin sekcie"
        >
          <Link
            v-for="item in navItems"
            :key="item.label"
            :href="item.href"
            class="rounded-r-lg border-l-2 border-transparent py-2.5 pl-4 pr-3 text-sm font-medium transition-colors"
            :class="isActive(item)
              ? 'border-primary-500 bg-primary-50 pl-[14px] text-primary-700'
              : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
          >
            {{ item.label }}
          </Link>
        </nav>
      </aside>

      <!-- Obsah stránky -->
      <main class="min-w-0 flex-1">
        <div class="mb-6 lg:hidden">
          <label for="admin-nav" class="sr-only">Sekcia</label>
          <select
            id="admin-nav"
            class="input-field w-full rounded-xl bg-white px-4 py-3 text-sm"
            @change="goToAdminSection($event.target.value)"
          >
            <option
              v-for="item in navItems"
              :key="item.label"
              :value="item.href"
              :selected="isActive(item)"
            >
              {{ item.label }}
            </option>
          </select>
        </div>
        <slot />
      </main>
    </div>
  </AuthenticatedLayout>
</template>
