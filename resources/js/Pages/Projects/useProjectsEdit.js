import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useProjectsEdit(project) {
  const form = useForm({
    name: project.name,
    client_name: project.client_name || '',
    is_active: project.is_active,
    user_ids: project.user_ids ?? [],
  });
  const submit = () => form.patch(route('projects.update', project.id));
  return { route, form, submit };
}
