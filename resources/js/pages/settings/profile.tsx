import { Form, Head, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile({ status }: { status?: string }) {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Perfil"
                    description="Actualizá los datos que identifican tu cuenta"
                />

                <Form
                    action={ProfileController.update.url()}
                    method="patch"
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="nombre">Nombre</Label>

                                <Input
                                    id="nombre"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.nombre}
                                    name="nombre"
                                    required
                                    autoComplete="name"
                                    placeholder="Nombre"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.nombre}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="apellido">Apellido</Label>
                                <Input
                                    id="apellido"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.apellido}
                                    name="apellido"
                                    required
                                    autoComplete="family-name"
                                    placeholder="Apellido"
                                />
                                <InputError
                                    className="mt-2"
                                    message={errors.apellido}
                                />
                            </div>

                            {status === 'profile-information-updated' && (
                                <div className="text-sm font-medium text-green-600">
                                    Perfil actualizado.
                                </div>
                            )}

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Guardar
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
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
