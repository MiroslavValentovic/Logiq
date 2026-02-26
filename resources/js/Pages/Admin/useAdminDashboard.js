const route = (name, ...params) => window.route(name, ...params);

export function useAdminDashboard() {
  return { route };
}
