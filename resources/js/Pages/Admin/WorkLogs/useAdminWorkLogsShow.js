const route = (name, ...params) => window.route(name, ...params);

export function useAdminWorkLogsShow() {
  return { route };
}
