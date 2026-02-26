import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useAdminUsersEdit(user) {
  const form = useForm({
    first_name: user.first_name ?? '',
    last_name: user.last_name ?? '',
    email: user.email,
    password: '',
    password_confirmation: '',
    project_ids: user.project_ids ?? [],
    is_admin: user.is_admin,
  });

  const submit = () => {
    form.transform((data) => {
      const { password, password_confirmation, ...rest } = data;
      if (!password || password === '') return rest;
      return { ...rest, password, password_confirmation };
    });
    form.patch(route('admin.users.update', user.id));
  };

  return { route, form, submit };
}
