import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useProjectsIndex() {
  const deleteModalOpen = ref(false);
  const toDeleteProjectId = ref(null);
  const showDeleteModal = (id) => {
    toDeleteProjectId.value = id;
    deleteModalOpen.value = true;
  };
  const closeDeleteModal = () => {
    deleteModalOpen.value = false;
    toDeleteProjectId.value = null;
  };
  const confirmDelete = () => {
    if (!toDeleteProjectId.value) return;
    router.delete(route('projects.destroy', toDeleteProjectId.value));
    closeDeleteModal();
  };
  return { route, showDeleteModal, deleteModalOpen, closeDeleteModal, confirmDelete };
}
