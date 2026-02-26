<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Pages/Layouts/GuestLayout.vue';
import { useRegister } from './useRegister';

const { route, form, submit } = useRegister();
</script>

<template>
  <GuestLayout title="Vytvorenie účtu">
    <form @submit.prevent="submit" class="space-y-5">
      <div>
        <label for="first_name" class="block text-sm font-semibold text-slate-700">Meno</label>
        <input
          id="first_name"
          v-model="form.first_name"
          type="text"
          required
          autofocus
          autocomplete="given-name"
          class="input-field mt-1.5 border px-4 py-2.5"
        />
        <p v-if="form.errors.first_name" class="mt-1.5 text-sm text-red-600">{{ form.errors.first_name }}</p>
      </div>
      <div>
        <label for="last_name" class="block text-sm font-semibold text-slate-700">Priezvisko</label>
        <input
          id="last_name"
          v-model="form.last_name"
          type="text"
          required
          autocomplete="family-name"
          class="input-field mt-1.5 border px-4 py-2.5"
        />
        <p v-if="form.errors.last_name" class="mt-1.5 text-sm text-red-600">{{ form.errors.last_name }}</p>
      </div>
      <div>
        <label for="email" class="block text-sm font-semibold text-slate-700">E-mail</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          autocomplete="username"
          class="input-field mt-1.5 border px-4 py-2.5"
        />
        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
      </div>
      <div>
        <label for="password" class="block text-sm font-semibold text-slate-700">Heslo</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          autocomplete="new-password"
          class="input-field mt-1.5 border px-4 py-2.5"
        />
        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
      </div>
      <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm password</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          autocomplete="new-password"
          class="input-field mt-1.5 border px-4 py-2.5"
        />
      </div>
      <div class="pt-2">
        <button type="submit" class="btn-primary w-full" :disabled="form.processing">
          Registrovať sa
        </button>
      </div>
    </form>
    <template #footer>
      Already have an account?
      <Link :href="route('login')" class="font-semibold text-primary-600 hover:text-primary-700">Log in</Link>
    </template>
  </GuestLayout>
</template>
