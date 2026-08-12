import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Checkbox } from '@/components/ui/checkbox';
import { EureButtonPrimary, EureInputGroup } from '@/components/ui/eure';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/login';

type Props = {
    defaultLogin: string;
    defaultPassword: string;
    status?: string;
};

export default function Login({
    defaultLogin,
    defaultPassword,
    status,
}: Props) {
    return (
        <>
            <Head title="Iniciar sesión" />

            <Form
                action={store.url()}
                method="post"
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <EureInputGroup
                            label="Usuario"
                            name="login"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="username"
                            defaultValue={defaultLogin}
                            placeholder="usuario"
                            error={!!errors.login}
                            errorMessage={errors.login}
                        />

                        <div className="grid gap-2">
                            <Label htmlFor="password">Contraseña</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                tabIndex={2}
                                autoComplete="current-password"
                                defaultValue={defaultPassword}
                                placeholder="Contraseña"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center space-x-3">
                            <Checkbox
                                id="remember"
                                name="remember"
                                tabIndex={3}
                            />
                            <Label htmlFor="remember">Recordarme</Label>
                        </div>

                        <EureButtonPrimary
                            type="submit"
                            className="mt-4 w-full"
                            tabIndex={4}
                            loading={processing}
                            data-test="login-button"
                        >
                            Ingresar
                        </EureButtonPrimary>
                    </div>
                )}
            </Form>

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
        </>
    );
}

Login.layout = {
    title: 'Iniciar sesión',
    description: 'Ingresá tu usuario y contraseña',
};
