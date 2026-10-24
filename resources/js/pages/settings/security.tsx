import { Form, Head } from '@inertiajs/react';
import { SaveIcon, ShieldCheckIcon } from 'lucide-react';
import { useRef } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import EducatioCard from '@/components/educatio-card';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';

type Props = { passwordRules: string };

export default function Security(props: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <>
            <Head title="Seguridad" />

            <EducatioCard
                title="Cambiar contraseña"
                icon={ShieldCheckIcon}
                description="Usá una contraseña larga y aleatoria para mantener tu cuenta segura."
            >
                <Form
                    action={SecurityController.update.url()}
                    method="put"
                    options={{
                        preserveScroll: true,
                    }}
                    resetOnError={[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]}
                    resetOnSuccess
                    onError={(errors) => {
                        if (errors.password) {
                            passwordInput.current?.focus();
                        }

                        if (errors.current_password) {
                            currentPasswordInput.current?.focus();
                        }
                    }}
                    className="space-y-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="current_password">
                                    Contraseña actual
                                </Label>

                                <PasswordInput
                                    id="current_password"
                                    autoFocus
                                    ref={currentPasswordInput}
                                    name="current_password"
                                    className="block w-full"
                                    autoComplete="current-password"
                                    placeholder="Contraseña actual"
                                />

                                <InputError message={errors.current_password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    Nueva contraseña
                                </Label>

                                <PasswordInput
                                    id="password"
                                    ref={passwordInput}
                                    name="password"
                                    className="block w-full"
                                    autoComplete="new-password"
                                    placeholder="Nueva contraseña"
                                    passwordrules={props.passwordRules}
                                />

                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirmar contraseña
                                </Label>

                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    className="block w-full"
                                    autoComplete="new-password"
                                    placeholder="Confirmar contraseña"
                                    passwordrules={props.passwordRules}
                                />

                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <div className="flex flex-wrap items-center justify-end gap-3 border-t border-border pt-4">
                                <EureButtonPrimary
                                    type="submit"
                                    loading={processing}
                                    className="text-primary-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    disabled={processing}
                                    data-test="update-password-button"
                                >
                                    <SaveIcon
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    Guardar
                                </EureButtonPrimary>
                            </div>
                        </>
                    )}
                </Form>
            </EducatioCard>
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Seguridad',
            href: edit(),
        },
    ],
};
