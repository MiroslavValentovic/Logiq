const route = (name, ...params) => window.route(name, ...params);

export function useAdminProjectsIndex() {
  return { route };
}
