import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useWorkLogsEdit(workLog) {
  const form = useForm({
    project_id: workLog.project_id,
    work_date: workLog.work_date?.slice(0, 10) ?? workLog.work_date,
    minutes: workLog.minutes,
    note: workLog.note || '',
  });
  const submit = (hours) => {
    form.minutes = Math.round(Number(hours) * 60);
    form.patch(route('work-logs.update', workLog.id));
  };
  const cancelUrl = workLog.work_date
    ? route('work-logs.index', { year: new Date(workLog.work_date).getFullYear(), month: new Date(workLog.work_date).getMonth() + 1 })
    : route('work-logs.index');
  return { route, form, submit, cancelUrl };
}
