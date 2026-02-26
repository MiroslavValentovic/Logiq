const route = (name, ...params) => window.route(name, ...params);

export function useAdminProjectsShow() {
  return { route };
}
