import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useAdminUsersCreate() {
  const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    project_ids: [],
    is_admin: false,
  });
  const submit = () => form.post(route('admin.users.store'));
  return { route, form, submit };
}
