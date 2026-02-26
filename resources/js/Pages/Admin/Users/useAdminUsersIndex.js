import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useAdminUsersIndex() {
  const deleteModalOpen = ref(false);
  const toDeleteUserId = ref(null);
  const showDeleteModal = (id) => {
    toDeleteUserId.value = id;
    deleteModalOpen.value = true;
  };
  const closeDeleteModal = () => {
    deleteModalOpen.value = false;
    toDeleteUserId.value = null;
  };
  const confirmDelete = () => {
    if (!toDeleteUserId.value) return;
    router.delete(route('admin.users.destroy', toDeleteUserId.value));
    closeDeleteModal();
  };
  return { route, showDeleteModal, deleteModalOpen, closeDeleteModal, confirmDelete };
}
