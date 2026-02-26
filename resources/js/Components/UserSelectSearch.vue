<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  users: { type: Array, default: () => [] },
  modelValue: { type: [Number, String], default: null },
  placeholder: { type: String, default: 'Vyberte používateľa' },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const containerRef = ref(null);

function onDocumentClick(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) isOpen.value = false;
}

onMounted(() => document.addEventListener('click', onDocumentClick));
onUnmounted(() => document.removeEventListener('click', onDocumentClick));

const selectedUser = computed(() => props.users.find((u) => Number(u.id) === Number(props.modelValue)));

const filteredUsers = computed(() => {
  const q = (searchQuery.value || '').trim().toLowerCase();
  if (!q) return props.users;
  return props.users.filter(
    (u) =>
      (u.name || '').toLowerCase().includes(q) ||
      (u.email || '').toLowerCase().includes(q)
  );
});

function select(user) {
  emit('update:modelValue', user.id);
  isOpen.value = false;
  searchQuery.value = '';
}

watch(isOpen, (open) => {
  if (open) searchQuery.value = '';
});
</script>

<template>
  <div ref="containerRef" class="relative inline-block min-w-[220px] max-w-full">
    <button
      type="button"
      class="flex w-full items-center justify-between gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 pr-8 text-left text-sm text-slate-900 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
      :class="isOpen && 'border-primary-500 ring-1 ring-primary-500'"
      @click="isOpen = !isOpen"
    >
      <span class="truncate">{{ selectedUser ? selectedUser.name : placeholder }}</span>
      <svg class="absolute right-2.5 h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <div
      v-show="isOpen"
      class="absolute left-0 top-full z-20 mt-1 w-full min-w-[280px] rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
    >
      <div class="border-b border-slate-100 px-2 pb-2">
        <input
          v-model="searchQuery"
          type="text"
          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500"
          placeholder="Hľadať podľa mena alebo e-mailu..."
          autocomplete="off"
          @click.stop
        />
      </div>
      <div class="max-h-60 overflow-y-auto py-1">
        <button
          v-for="u in filteredUsers"
          :key="u.id"
          type="button"
          class="flex w-full flex-col items-start gap-0.5 px-3 py-2.5 text-left text-sm transition hover:bg-slate-50"
          :class="Number(modelValue) === Number(u.id) && 'bg-primary-50 text-primary-800'"
          @click="select(u)"
        >
          <span class="font-medium">{{ u.name }}</span>
          <span v-if="u.email" class="text-xs text-slate-500">{{ u.email }}</span>
        </button>
        <p v-if="filteredUsers.length === 0" class="px-3 py-4 text-center text-sm text-slate-500">Žiadny používateľ</p>
      </div>
    </div>
  </div>
</template>
