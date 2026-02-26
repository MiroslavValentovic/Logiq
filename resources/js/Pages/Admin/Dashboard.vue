<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Layouts/AdminLayout.vue';

defineProps({ usersCount: Number, projectsCount: Number });
const route = (name, ...params) => window.route(name, ...params);
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="page-title">Nastavenia</h1>
    </template>

    <div class="space-y-10">
      <p class="max-w-xl text-slate-600">
        Spravujte používateľov, projekty a prehliadajte záznamy práce.
      </p>

      <div class="grid gap-4 sm:grid-cols-2">
        <Link
          :href="route('admin.users.index')"
          class="group flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 transition hover:ring-primary-200 hover:shadow-md"
        >
          <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </span>
          <div class="min-w-0 flex-1">
            <span class="font-semibold text-slate-900">Používatelia</span>
            <p class="text-sm text-slate-500">{{ usersCount }} celkom</p>
          </div>
          <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Link>
        <Link
          :href="route('admin.projects.index')"
          class="group flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 transition hover:ring-primary-200 hover:shadow-md"
        >
          <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
          </span>
          <div class="min-w-0 flex-1">
            <span class="font-semibold text-slate-900">Projekty</span>
            <p class="text-sm text-slate-500">{{ projectsCount }} celkom</p>
          </div>
          <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>
