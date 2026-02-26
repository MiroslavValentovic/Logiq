import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useLogin() {
  const form = useForm({
    email: '',
    password: '',
    remember: false,
  });
  const submit = () => form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
  return { route, form, submit };
}
