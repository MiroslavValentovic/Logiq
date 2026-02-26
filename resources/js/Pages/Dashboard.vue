<script setup>
import AuthenticatedLayout from '@/Pages/Layouts/AuthenticatedLayout.vue';
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h1 class="page-title">Prehľad</h1>
    </template>

    <div class="card p-8">
      <p class="text-slate-600">Ste prihlásený. V menu môžete spravovať projekty, evidovať čas a prezeráť reporty.</p>
    </div>
  </AuthenticatedLayout>
</template>
