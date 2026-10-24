import { Link } from '@inertiajs/react';
import {
    PaletteIcon,
    SettingsIcon,
    ShieldCheckIcon,
    UserRoundIcon,
} from 'lucide-react';
import type { PropsWithChildren } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Perfil',
        href: edit(),
        icon: UserRoundIcon,
    },
    {
        title: 'Seguridad',
        href: editSecurity(),
        icon: ShieldCheckIcon,
    },
    {
        title: 'Apariencia',
        href: editAppearance(),
        icon: PaletteIcon,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="mx-auto flex w-full max-w-5xl flex-col gap-4">
            <EducatioCard>
                <header className="flex items-center gap-3">
                    <SettingsIcon
                        className="size-8 shrink-0 text-educatio-icon"
                        aria-hidden="true"
                    />
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                            Configuración
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Administrá tu perfil y las opciones de tu cuenta.
                        </p>
                    </div>
                </header>
            </EducatioCard>

            <div className="grid gap-4 lg:grid-cols-[12rem_minmax(0,1fr)] lg:items-start">
                <aside className="min-w-0">
                    <EducatioCard className="p-2">
                        <nav
                            className="grid gap-1 sm:grid-cols-3 lg:grid-cols-1"
                            aria-label="Configuración personal"
                        >
                            {sidebarNavItems.map((item, index) => (
                                <Button
                                    key={`${toUrl(item.href)}-${index}`}
                                    size="sm"
                                    variant="ghost"
                                    asChild
                                    className={cn('w-full justify-start', {
                                        'bg-muted text-foreground':
                                            isCurrentOrParentUrl(item.href),
                                    })}
                                >
                                    <Link
                                        href={item.href}
                                        aria-current={
                                            isCurrentOrParentUrl(item.href)
                                                ? 'page'
                                                : undefined
                                        }
                                    >
                                        {item.icon && (
                                            <item.icon
                                                className="size-4 text-educatio-icon"
                                                aria-hidden="true"
                                            />
                                        )}
                                        {item.title}
                                    </Link>
                                </Button>
                            ))}
                        </nav>
                    </EducatioCard>
                </aside>

                <div className="min-w-0 space-y-4">{children}</div>
            </div>
        </div>
    );
}
