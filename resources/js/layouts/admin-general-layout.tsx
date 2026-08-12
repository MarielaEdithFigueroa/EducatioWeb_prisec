import { Link, usePage } from '@inertiajs/react';
import {
    CalendarCheckIcon,
    ClipboardListIcon,
    CoinsIcon,
    DatabaseIcon,
    FileTextIcon,
    GraduationCapIcon,
    LandmarkIcon,
    LayersIcon,
    LayoutDashboardIcon,
    ListTreeIcon,
    NotebookTextIcon,
    ReceiptIcon,
    SettingsIcon,
    ShieldIcon,
    TriangleAlertIcon,
    UserCogIcon,
    UsersIcon,
    WalletIcon,
} from 'lucide-react';
import { useState } from 'react';
import type { PropsWithChildren } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useInitials } from '@/hooks/use-initials';
import {
    RoleLayoutFrame,
    RoleSidebar,
    SidebarUserFooter,
} from '@/layouts/role-shell';
import type { DisplayBreadcrumb, RoleNavItem } from '@/layouts/role-shell';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as cursosIndex } from '@/routes/academico/cursos';
import { index as divisionesIndex } from '@/routes/academico/divisiones';
import { index as nivelesIndex } from '@/routes/academico/niveles';
import { index as planesEstudioIndex } from '@/routes/academico/planes_estudio';
import { index as turnosIndex } from '@/routes/academico/turnos';
import type { BreadcrumbItem } from '@/types';

interface Props extends PropsWithChildren {
    breadcrumbs?: BreadcrumbItem[];
}

type AppMenuItem = {
    label: string;
    description?: string;
    icon: RoleNavItem['icon'];
    href?: string;
    disabled?: boolean;
};

type AppMenuSection = {
    title: string;
    icon: RoleNavItem['icon'];
    items: AppMenuItem[];
};

const NAV_ITEMS: RoleNavItem[] = [
    { label: 'Inicio', href: dashboard.url(), icon: LayoutDashboardIcon },
];

const APP_MENU_SECTIONS: AppMenuSection[] = [
    {
        title: 'Académico',
        icon: GraduationCapIcon,
        items: [
            {
                label: 'Niveles',
                description: 'Inicial, primaria y secundaria',
                icon: GraduationCapIcon,
                href: nivelesIndex.url(),
            },
            {
                label: 'Planes de estudio',
                description: 'Planes por nivel educativo',
                icon: NotebookTextIcon,
                href: planesEstudioIndex.url(),
            },
            {
                label: 'Turnos',
                description: 'Organización horaria',
                icon: CalendarCheckIcon,
                href: turnosIndex.url(),
            },
            {
                label: 'Cursos',
                description: 'Salas, grados y años',
                icon: ListTreeIcon,
                href: cursosIndex.url(),
            },
            {
                label: 'Divisiones',
                description: 'Divisiones de los grupos',
                icon: LayersIcon,
                href: divisionesIndex.url(),
            },
            {
                label: 'Alumnos',
                description: 'Legajos y matrícula',
                icon: UsersIcon,
                disabled: true,
            },
            {
                label: 'Responsables',
                description: 'Padres, madres y tutores',
                icon: UserCogIcon,
                disabled: true,
            },
            {
                label: 'Grupos',
                description: 'Comisiones por ciclo lectivo',
                icon: LayersIcon,
                disabled: true,
            },
            {
                label: 'Materias',
                description: 'Espacios curriculares',
                icon: NotebookTextIcon,
                disabled: true,
            },
            {
                label: 'Calificaciones',
                description: 'Notas por período',
                icon: ClipboardListIcon,
                disabled: true,
            },
            {
                label: 'Asistencia',
                description: 'Presencias y faltas',
                icon: CalendarCheckIcon,
                disabled: true,
            },
            {
                label: 'Boletines',
                description: 'Reportes por período',
                icon: FileTextIcon,
                disabled: true,
            },
        ],
    },
    {
        title: 'Administrativo',
        icon: ReceiptIcon,
        items: [
            {
                label: 'Cuentas corrientes',
                description: 'Saldos de alumnos y empresas',
                icon: WalletIcon,
                disabled: true,
            },
            {
                label: 'Facturación',
                description: 'Comprobantes electrónicos',
                icon: ReceiptIcon,
                disabled: true,
            },
            {
                label: 'Cobranzas',
                description: 'Recibos y cobros',
                icon: CoinsIcon,
                disabled: true,
            },
            {
                label: 'Caja',
                description: 'Apertura, cierre y movimientos',
                icon: FileTextIcon,
                disabled: true,
            },
            {
                label: 'Morosos',
                description: 'Deuda vencida',
                icon: TriangleAlertIcon,
                disabled: true,
            },
        ],
    },
    {
        title: 'Sistema',
        icon: DatabaseIcon,
        items: [
            {
                label: 'Usuarios',
                description: 'Accesos',
                icon: ShieldIcon,
                disabled: true,
            },
            {
                label: 'Configuración',
                description: 'Parámetros de la institución',
                icon: SettingsIcon,
                disabled: true,
            },
            {
                label: 'Bancos',
                description: 'Entidades bancarias',
                icon: LandmarkIcon,
                disabled: true,
            },
            {
                label: 'Auditoría',
                description: 'Actividad y trazabilidad',
                icon: DatabaseIcon,
                disabled: true,
            },
        ],
    },
];

function AppGlobalMenu() {
    const renderItem = (item: AppMenuItem) => {
        const Icon = item.icon;
        const className = cn(
            'gap-2',
            item.disabled && 'cursor-not-allowed opacity-45',
        );
        const content = (
            <>
                <Icon className="size-4" />
                <span>{item.label}</span>
            </>
        );

        if (item.disabled || !item.href) {
            return (
                <DropdownMenuItem
                    key={item.label}
                    disabled
                    className={className}
                >
                    {content}
                </DropdownMenuItem>
            );
        }

        return (
            <DropdownMenuItem key={item.label} asChild className={className}>
                <Link href={item.href}>{content}</Link>
            </DropdownMenuItem>
        );
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button
                    type="button"
                    aria-label="Abrir menú general"
                    className="inline-flex size-10 items-center justify-center rounded-md text-shell-foreground transition-colors hover:bg-shell-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                    <ListTreeIcon className="size-5" />
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                align="start"
                sideOffset={10}
                className="w-72 rounded-lg p-1 shadow-xl"
            >
                <DropdownMenuItem asChild>
                    <Link href={dashboard.url()}>
                        <LayoutDashboardIcon className="size-4" />
                        Inicio
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                {APP_MENU_SECTIONS.map((section) => {
                    const SectionIcon = section.icon;

                    return (
                        <DropdownMenuSub key={section.title}>
                            <DropdownMenuSubTrigger>
                                <SectionIcon className="mr-2 size-4" />
                                {section.title}
                            </DropdownMenuSubTrigger>
                            <DropdownMenuSubContent className="w-64">
                                {section.items.map(renderItem)}
                            </DropdownMenuSubContent>
                        </DropdownMenuSub>
                    );
                })}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

export default function AdminGeneralLayout({
    children,
    breadcrumbs = [],
}: Props) {
    const { auth } = usePage().props;
    const { url } = usePage();
    const getInitials = useInitials();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const userName = `${auth.user.nombre} ${auth.user.apellido}`;
    const displayBreadcrumbs: DisplayBreadcrumb[] =
        breadcrumbs.length > 0
            ? breadcrumbs
            : [{ title: 'Inicio', href: dashboard.url() }];

    return (
        <div className="min-h-screen bg-background text-foreground">
            <RoleSidebar
                url={url}
                roleCaption="EureFramework"
                navAriaLabel="Navegación principal"
                navItems={NAV_ITEMS}
                useRealLinks
                open={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
                footer={
                    <SidebarUserFooter
                        userInitials={getInitials(userName)}
                        userName={userName}
                        roleLabel="Usuario"
                    />
                }
            />
            <RoleLayoutFrame
                sidebarOpen={sidebarOpen}
                onToggleSidebar={() => setSidebarOpen((open) => !open)}
                breadcrumbs={displayBreadcrumbs}
                headerExtra={<AppGlobalMenu />}
                userInitials={getInitials(userName)}
                user={auth.user}
            >
                {children}
            </RoleLayoutFrame>
        </div>
    );
}
