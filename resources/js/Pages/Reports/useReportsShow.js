const route = (name, ...params) => window.route(name, ...params);

export function useReportsShow() {
  return { route };
}
