import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

const currentYear = new Date().getFullYear();

export function useReportsIndex(props) {
  const filterYear = ref(props.year ?? currentYear);
  const filterMonth = ref(props.month ?? new Date().getMonth() + 1);

  watch(
    () => [props.year, props.month],
    ([y, m]) => {
      filterYear.value = y;
      filterMonth.value = m;
    }
  );

  const isMinMonth = computed(() => filterYear.value === currentYear && filterMonth.value === 1);

  function goPrev(userId = null) {
    if (isMinMonth.value) return;
    let y = filterYear.value;
    let m = filterMonth.value - 1;
    if (m < 1) {
      m = 12;
      y -= 1;
    }
    filterYear.value = y;
    filterMonth.value = m;
    submitFilter(userId);
  }

  function goNext(userId = null) {
    let y = filterYear.value;
    let m = filterMonth.value + 1;
    if (m > 12) {
      m = 1;
      y += 1;
    }
    filterYear.value = y;
    filterMonth.value = m;
    submitFilter(userId);
  }

  function goToday(userId = null) {
    const n = new Date();
    filterYear.value = n.getFullYear();
    filterMonth.value = n.getMonth() + 1;
    submitFilter(userId);
  }

  const submitFilter = (userId = null) => {
    let y = filterYear.value;
    let mo = filterMonth.value;
    if (y < currentYear || (y === currentYear && mo < 1)) {
      y = currentYear;
      mo = 1;
    }
    const params = { year: y, month: mo };
    if (userId != null) params.user_id = userId;
    router.get(route('reports.index'), params, { preserveScroll: true, replace: true });
  };

  const lock = () => {
    const data = { year: props.year, month: props.month };
    if (props.selectedUserId != null) data.user_id = props.selectedUserId;
    router.post(route('reports.lock'), data);
  };

  const unlock = () => {
    const data = { year: props.year, month: props.month };
    if (props.selectedUserId != null) data.user_id = props.selectedUserId;
    router.post(route('reports.unlock'), data);
  };

  return { route, filterYear, filterMonth, submitFilter, lock, unlock, currentYear, isMinMonth, goPrev, goNext, goToday };
}
