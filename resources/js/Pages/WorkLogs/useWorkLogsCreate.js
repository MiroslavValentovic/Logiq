import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useWorkLogsCreate(initialDate = '') {
  const form = useForm({
    project_id: '',
    work_date: initialDate || new Date().toISOString().slice(0, 10),
    minutes: 0,
    note: '',
  });
  const submit = (hours) => {
    form.minutes = Math.round(Number(hours) * 60);
    form.post(route('work-logs.store'));
  };
  return { route, form, submit };
}
