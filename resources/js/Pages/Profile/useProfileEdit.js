import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

function getInitialProfile(user) {
  const u = user?.value ?? user;
  return {
    first_name: (u && (u.first_name != null) ? u.first_name : '') || '',
    last_name: (u && (u.last_name != null) ? u.last_name : '') || '',
    email: (u && (u.email != null) ? u.email : '') || '',
    avatar: null,
  };
}

export function useProfileEdit(userOrRef) {
  const profileForm = useForm(getInitialProfile(userOrRef));

  const profileSubmitting = ref(false);
  const updateProfile = () => {
    const first_name = profileForm.first_name;
    const last_name = profileForm.last_name;
    const email = profileForm.email;
    const avatar = profileForm.avatar;

    const visitOptions = {
      preserveScroll: true,
      onError: (errors) => profileForm.setErrors(errors),
      onFinish: () => { profileSubmitting.value = false; },
    };

    profileSubmitting.value = true;
    if (avatar instanceof File) {
      const formData = new FormData();
      formData.append('_method', 'PATCH');
      formData.append('first_name', first_name);
      formData.append('last_name', last_name);
      formData.append('email', email);
      formData.append('avatar', avatar);
      router.post(route('profile.update'), formData, { ...visitOptions, forceFormData: true });
    } else {
      router.patch(route('profile.update'), {
        first_name,
        last_name,
        email,
      }, visitOptions);
    }
  };

  const passwordForm = useForm({
    password: '',
    password_confirmation: '',
  });
  const updatePassword = () => passwordForm.put(route('password.update'), {
    onFinish: () => passwordForm.reset(),
  });

  const deleteForm = useForm({ password: '' });
  const deleteModalOpen = ref(false);
  const showDeleteModal = () => { deleteModalOpen.value = true; };
  const closeDeleteModal = () => { deleteModalOpen.value = false; };
  const deleteAccount = () => deleteForm.delete(route('profile.destroy'), {
    onFinish: () => closeDeleteModal(),
  });

  return {
    route,
    profileForm,
    profileSubmitting,
    updateProfile,
    passwordForm,
    updatePassword,
    deleteForm,
    deleteAccount,
    deleteModalOpen,
    showDeleteModal,
    closeDeleteModal,
  };
}
