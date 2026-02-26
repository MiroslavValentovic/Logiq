<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  projects: { type: Array, default: () => [] },
  modelValue: { type: Array, default: () => [] },
  label: { type: String, default: 'Projekty' },
  searchPlaceholder: { type: String, default: 'Hľadať...' },
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

const selectedCount = computed(() => (props.modelValue || []).length);

const filteredProjects = computed(() => {
  const q = (searchQuery.value || '').trim().toLowerCase();
  if (!q) return props.projects;
  return props.projects.filter(
    (p) =>
      (p.name || '').toLowerCase().includes(q) ||
      (p.client_name || '').toLowerCase().includes(q)
  );
});

function toggle(projectId) {
  const ids = new Set(props.modelValue || []);
  if (ids.has(projectId)) ids.delete(projectId);
  else ids.add(projectId);
  emit('update:modelValue', Array.from(ids));
}

function isChecked(projectId) {
  return (props.modelValue || []).includes(projectId);
}
</script>

<template>
  <div ref="containerRef" class="relative">
    <label class="block text-sm font-semibold text-slate-700">{{ label }}</label>
    <button
      type="button"
      class="mt-1.5 flex w-full items-center justify-between gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-9 text-left text-sm text-slate-900 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
      :class="isOpen && 'border-primary-500 ring-1 ring-primary-500'"
      @click="isOpen = !isOpen"
    >
      <span class="truncate">
        {{ selectedCount ? `Vybraných: ${selectedCount}` : 'Kliknite pre výber projektov' }}
      </span>
      <svg class="absolute right-2.5 h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <div
      v-show="isOpen"
      class="absolute left-0 top-full z-20 mt-1 w-full min-w-[280px] rounded-lg border border-slate-200 bg-white py-2 shadow-lg"
    >
      <div class="border-b border-slate-100 px-2 pb-2">
        <input
          v-model="searchQuery"
          type="text"
          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500"
          :placeholder="searchPlaceholder"
          autocomplete="off"
          @click.stop
        />
      </div>
      <div class="max-h-56 overflow-y-auto py-1">
        <label
          v-for="p in filteredProjects"
          :key="p.id"
          class="flex cursor-pointer items-start gap-3 px-3 py-2.5 text-left text-sm transition hover:bg-slate-50"
        >
          <input
            type="checkbox"
            :checked="isChecked(p.id)"
            class="mt-1 h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
            @change="toggle(p.id)"
          />
          <div class="min-w-0 flex-1">
            <span class="block font-medium text-slate-900">{{ p.name }}</span>
            <span v-if="p.client_name" class="block text-xs text-slate-500">{{ p.client_name }}</span>
          </div>
        </label>
        <p v-if="filteredProjects.length === 0" class="py-4 text-center text-sm text-slate-500">Žiadny projekt</p>
      </div>
    </div>
  </div>
</template>
