import { useForm } from '@inertiajs/vue3';

const route = (name, ...params) => window.route(name, ...params);

export function useRegister() {
  const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const submit = () => form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
  return { route, form, submit };
}
