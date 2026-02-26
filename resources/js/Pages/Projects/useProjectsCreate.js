import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useProjectsCreate() {
  const form = useForm({
    name: '',
    client_name: '',
    is_active: true,
    user_ids: [],
  });
  const submit = () => form.post(route('projects.store'));
  return { route, form, submit };
}
