<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Pages/Layouts/GuestLayout.vue';
import { useLogin } from './useLogin';

const { route, form, submit } = useLogin();
</script>

<template>
  <GuestLayout title="Prihlásenie">
    <form @submit.prevent="submit" class="space-y-5">
      <div>
        <label for="email" class="block text-sm font-semibold text-slate-700">E-mail</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          autofocus
          autocomplete="username"
          class="input-field mt-1.5 border px-4 py-2.5"
        />
        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
      </div>
      <div>
        <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          autocomplete="current-password"
          class="input-field mt-1.5 border px-4 py-2.5"
        />
        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
      </div>
      <div class="flex items-center gap-2">
        <input
          id="remember"
          v-model="form.remember"
          type="checkbox"
          class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
        />
        <label for="remember" class="text-sm text-slate-600">Zapamätať si ma</label>
      </div>
      <div class="flex items-center justify-between gap-4 pt-2">
        <Link :href="route('password.request')" class="text-sm font-medium text-primary-600 hover:text-primary-700">
          Zabudli ste heslo?
        </Link>
        <button type="submit" class="btn-primary" :disabled="form.processing">
          Prihlásiť sa
        </button>
      </div>
    </form>
  </GuestLayout>
</template>
