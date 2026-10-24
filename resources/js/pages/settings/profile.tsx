import { Form, Head, usePage } from '@inertiajs/react';
import { SaveIcon, UserRoundIcon } from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import EducatioCard from '@/components/educatio-card';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile({ status }: { status?: string }) {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Perfil" />

            <EducatioCard
                title="Perfil"
                icon={UserRoundIcon}
                description="Actualizá los datos que identifican tu cuenta."
            >
                <Form
                    action={ProfileController.update.url()}
                    method="patch"
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <EureInputGroup
                                label="Nombre"
                                name="nombre"
                                defaultValue={auth.user.nombre}
                                required
                                autoFocus
                                autoComplete="name"
                                placeholder="Nombre"
                                error={Boolean(errors.nombre)}
                                errorMessage={errors.nombre}
                            />

                            <EureInputGroup
                                label="Apellido"
                                name="apellido"
                                defaultValue={auth.user.apellido}
                                required
                                autoComplete="family-name"
                                placeholder="Apellido"
                                error={Boolean(errors.apellido)}
                                errorMessage={errors.apellido}
                            />

                            {status === 'profile-information-updated' && (
                                <div
                                    role="status"
                                    className="text-sm font-medium text-eure-success"
                                >
                                    Perfil actualizado.
                                </div>
                            )}

                            <div className="flex flex-wrap items-center justify-end gap-3 border-t border-border pt-4">
                                <EureButtonPrimary
                                    type="submit"
                                    loading={processing}
                                    className="text-primary-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    disabled={processing}
                                    data-test="update-profile-button"
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

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Configuración de perfil',
            href: edit(),
        },
    ],
};
