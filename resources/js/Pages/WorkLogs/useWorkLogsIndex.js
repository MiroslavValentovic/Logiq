import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

const WEEKDAYS = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'];

function getCalendarGrid(year, month) {
  const first = new Date(year, month - 1, 1);
  const last = new Date(year, month, 0);
  const start = new Date(first);
  const dayOfWeek = (first.getDay() + 6) % 7;
  start.setDate(start.getDate() - dayOfWeek);
  const days = [];
  for (let i = 0; i < 42; i++) {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    const iso = d.toISOString().slice(0, 10);
    days.push({
      date: d.getDate(),
      iso,
      isCurrentMonth: d.getMonth() === month - 1,
      isToday: false,
    });
  }
  const today = new Date();
  const todayStr = today.toISOString().slice(0, 10);
  days.forEach((day) => {
    day.isToday = day.iso === todayStr;
  });
  const weeks = [];
  for (let w = 0; w < 6; w++) {
    weeks.push(days.slice(w * 7, (w + 1) * 7));
  }
  return weeks;
}

export function useWorkLogsIndex(props) {
  const filterYear = ref(props.year ?? new Date().getFullYear());
  const filterMonth = ref(props.month ?? new Date().getMonth() + 1);
  const calendarEvents = ref([]);
  const calendarLoading = ref(false);

  const currentYear = new Date().getFullYear();
  const currentView = computed(() => props.view || 'calendar');
  const year = computed(() => props.year ?? currentYear);
  const month = computed(() => props.month ?? new Date().getMonth() + 1);

  const isMinMonth = computed(() => year.value === currentYear && month.value === 1);

  const monthLabel = computed(() => {
    const d = new Date(year.value, month.value - 1, 1);
    return d.toLocaleDateString('sk-SK', { month: 'long', year: 'numeric' });
  });

  const calendarWeeks = computed(() => getCalendarGrid(year.value, month.value));

  const eventsByDay = computed(() => {
    const map = {};
    calendarEvents.value.forEach((ev) => {
      const key = ev.start?.slice(0, 10) ?? ev.start;
      if (!map[key]) map[key] = [];
      map[key].push(ev);
    });
    return map;
  });

  function getEventsForDay(iso) {
    return eventsByDay.value[iso] ?? [];
  }

  async function fetchCalendarEvents() {
    if (currentView.value !== 'calendar') return;
    const start = new Date(year.value, month.value - 1, 1);
    const end = new Date(year.value, month.value, 0);
    const startStr = start.toISOString().slice(0, 10);
    const endStr = end.toISOString().slice(0, 10);
    calendarLoading.value = true;
    try {
      const r = await fetch(
        `${route('work-logs.events')}?start=${encodeURIComponent(startStr)}&end=${encodeURIComponent(endStr)}`,
        { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }
      );
      const data = await r.json();
      calendarEvents.value = data;
    } catch {
      calendarEvents.value = [];
    } finally {
      calendarLoading.value = false;
    }
  }

  function goPrev() {
    if (isMinMonth.value) return;
    let y = year.value;
    let m = month.value - 1;
    if (m < 1) {
      m = 12;
      y -= 1;
    }
    router.get(route('work-logs.index'), { view: 'calendar', year: y, month: m }, { preserveState: true });
  }

  function goNext() {
    let y = year.value;
    let m = month.value + 1;
    if (m > 12) {
      m = 1;
      y += 1;
    }
    router.get(route('work-logs.index'), { view: 'calendar', year: y, month: m }, { preserveState: true });
  }

  function goToday() {
    const n = new Date();
    router.get(route('work-logs.index'), { view: 'calendar', year: n.getFullYear(), month: n.getMonth() + 1 }, { preserveState: true });
  }

  function go(view, y, m) {
    router.get(route('work-logs.index'), { view, year: y ?? year.value, month: m ?? month.value }, { preserveState: true });
  }

  const canAddToSelf = computed(() => !(props.usersForAdmin?.length > 0));

  const minDateStr = `${currentYear}-01-01`;
  function createUrl(dateStr) {
    if (!canAddToSelf.value || (dateStr && dateStr < minDateStr)) return '#';
    return route('work-logs.create') + '?date=' + (dateStr || minDateStr);
  }

  function editUrl(log) {
    return log.url || route('work-logs.edit', log.id);
  }

  let dayClickTimeout = null;
  function onDayClick(day) {
    if (dayClickTimeout) clearTimeout(dayClickTimeout);
    dayClickTimeout = setTimeout(() => {
      dayClickTimeout = null;
      const [y, m] = day.iso.split('-').map(Number);
      if (y === year.value && m === month.value) return;
      if (y < currentYear || (y === currentYear && m < 1)) return;
      router.get(route('work-logs.index'), { view: 'calendar', year: y, month: m }, { preserveState: true });
    }, 200);
  }

  function onDayDblclick(day) {
    if (dayClickTimeout) {
      clearTimeout(dayClickTimeout);
      dayClickTimeout = null;
    }
    if (!canAddToSelf.value) return;
    window.location.href = createUrl(day.iso);
  }

  const deleteModalOpen = ref(false);
  const toDeleteLogId = ref(null);
  const showDeleteModal = (id) => {
    toDeleteLogId.value = id;
    deleteModalOpen.value = true;
  };
  const closeDeleteModal = () => {
    deleteModalOpen.value = false;
    toDeleteLogId.value = null;
  };
  const confirmDelete = () => {
    if (!toDeleteLogId.value) return;
    router.delete(route('work-logs.destroy', toDeleteLogId.value));
    closeDeleteModal();
  };

  const submitFilter = () => {
    let y = filterYear.value;
    let mo = filterMonth.value;
    if (y < currentYear || (y === currentYear && mo < 1)) {
      y = currentYear;
      mo = 1;
    }
    router.get(route('work-logs.index'), { view: 'list', year: y, month: mo });
  };

  onMounted(() => {
    if (currentView.value === 'calendar') fetchCalendarEvents();
  });

  watch([() => props.view, () => props.year, () => props.month], () => {
    if (currentView.value === 'calendar') fetchCalendarEvents();
  });

  return {
    route,
    WEEKDAYS,
    currentView,
    filterYear,
    filterMonth,
    calendarWeeks,
    calendarEvents,
    calendarLoading,
    monthLabel,
    year,
    month,
    currentYear,
    isMinMonth,
    getEventsForDay,
    goPrev,
    goNext,
    goToday,
    go,
    createUrl,
    editUrl,
    onDayClick,
    onDayDblclick,
    showDeleteModal,
    deleteModalOpen,
    closeDeleteModal,
    confirmDelete,
    submitFilter,
  };
}
