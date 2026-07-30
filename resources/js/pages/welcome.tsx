import { Head, Link } from '@inertiajs/react';
import { login } from '@/routes';

export default function Welcome() {
    return (
        <>
            <Head title="EureFramework" />
            <main className="flex min-h-screen items-center justify-center bg-shell-public-background p-6">
                <section className="w-full max-w-lg rounded-xl bg-shell-public-surface p-10 text-center shadow-xl">
                    <div className="mx-auto mb-6 flex size-16 items-center justify-center rounded-full bg-shell-brand text-2xl font-extrabold text-shell-brand-foreground">
                        E
                    </div>
                    <h1 className="text-3xl font-bold">EureFramework</h1>
                    <p className="mt-3 text-muted-foreground">
                        Base técnica para aplicaciones Laravel, Inertia y React.
                    </p>
                    <Link
                        href={login()}
                        className="mt-8 inline-flex rounded-md bg-primary px-5 py-3 font-semibold text-primary-foreground"
                    >
                        Iniciar sesión
                    </Link>
                </section>
            </main>
        </>
    );
}
