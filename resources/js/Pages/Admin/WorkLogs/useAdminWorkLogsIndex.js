import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useAdminWorkLogsIndex(props) {
  const filterYear = ref(props.year ?? '');
  const filterMonth = ref(props.month ?? '');

  const submitFilter = () => {
    router.get(route('admin.work-logs.index'), { year: filterYear.value || undefined, month: filterMonth.value || undefined });
  };

  return { route, filterYear, filterMonth, submitFilter };
}
