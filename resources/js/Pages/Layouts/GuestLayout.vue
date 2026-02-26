<script setup>
defineProps({ title: String });
const route = (name, ...params) => window.route(name, ...params);
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 py-12">
    <div class="w-full max-w-md">
      <div class="card overflow-hidden p-8">
        <div class="mb-6 flex justify-center">
          <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-600 text-xl font-bold text-white">L</span>
        </div>
        <h1 class="text-center text-xl font-bold tracking-tight text-slate-900">{{ title }}</h1>
        <p class="mt-1 text-center text-sm text-slate-500">Logiq — záznamy práce a reporty</p>
        <div class="mt-8">
          <slot />
        </div>
      </div>
      <p class="mt-6 text-center text-sm text-slate-500">
        <slot name="footer" />
      </p>
    </div>
  </div>
</template>
