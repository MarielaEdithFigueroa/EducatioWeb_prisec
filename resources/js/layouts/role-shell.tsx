import { Link } from '@inertiajs/react';
import {
    BellIcon,
    ChevronRightIcon,
    LogOutIcon,
    MenuIcon,
    XIcon,
} from 'lucide-react';
import type { ComponentType, ReactNode } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { getUrlPath } from '@/lib/url';
import { cn } from '@/lib/utils';
import { dashboard, logout } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { User } from '@/types/auth';

export type RoleNavItem = {
    label: string;
    href: string;
    icon: ComponentType<{ className?: string }>;
    visible?: (user: User) => boolean;
};

export function visibleNavItems(
    items: RoleNavItem[],
    user: User,
): RoleNavItem[] {
    return items.filter((item) => !item.visible || item.visible(user));
}

export type DisplayBreadcrumb = {
    title: string;
    href: BreadcrumbItem['href'] | string;
};

export function isActiveNavItem(url: string, item: RoleNavItem) {
    const currentPath = getUrlPath(url);
    const itemPath = getUrlPath(item.href);

    return (
        item.href !== '#' &&
        (currentPath === itemPath || currentPath.startsWith(`${itemPath}/`))
    );
}

export function SidebarNavLink({
    item,
    active,
    onNavigate,
    real = false,
}: {
    item: RoleNavItem;
    active: boolean;
    onNavigate?: () => void;
    real?: boolean;
}) {
    const Icon = item.icon;
    const className = cn(
        'flex min-h-11 items-center gap-3 rounded-md border-l-3 px-3 py-2 text-sm font-medium transition-colors',
        active
            ? 'border-shell-active-border bg-shell-active text-shell-active-foreground'
            : 'border-transparent text-shell-sidebar-muted/90 hover:border-shell-sidebar-foreground/20 hover:bg-shell-sidebar-foreground/8 hover:text-shell-sidebar-foreground',
    );

    const content = (
        <>
            <Icon className="size-5 shrink-0" />
            <span className="leading-tight">{item.label}</span>
        </>
    );

    if (real) {
        return (
            <Link href={item.href} className={className} onClick={onNavigate}>
                {content}
            </Link>
        );
    }

    return (
        <a href={item.href} className={className} onClick={onNavigate}>
            {content}
        </a>
    );
}

export function SidebarUserFooter({
    userInitials,
    userName,
    roleLabel,
    scopeLabel,
    scopeValue,
}: {
    userInitials: string;
    userName: string;
    roleLabel: string;
    scopeLabel?: string;
    scopeValue?: string | null;
}) {
    return (
        <>
            {scopeLabel && (
                <>
                    <div>
                        <div className="text-[0.65rem] font-semibold tracking-wide text-shell-sidebar-muted/45 uppercase">
                            {scopeLabel}
                        </div>
                        <div className="truncate text-sm font-medium text-shell-sidebar-muted/85">
                            {scopeValue ?? '—'}
                        </div>
                    </div>
                    <div className="my-4 border-t border-shell-sidebar-foreground/20" />
                </>
            )}
            <div className="flex items-center gap-3">
                <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold tracking-wide text-primary-foreground uppercase">
                    {userInitials}
                </div>
                <div className="min-w-0">
                    <div className="truncate text-sm font-semibold">
                        {userName}
                    </div>
                    <div className="text-xs text-shell-sidebar-muted/65">
                        {roleLabel}
                    </div>
                </div>
            </div>
        </>
    );
}

export function RoleSidebar({
    url,
    roleCaption,
    navAriaLabel,
    navItems,
    useRealLinks = false,
    footer,
    open,
    onClose,
}: {
    url: string;
    roleCaption: string;
    navAriaLabel: string;
    navItems: RoleNavItem[];
    useRealLinks?: boolean;
    footer: ReactNode;
    open: boolean;
    onClose: () => void;
}) {
    return (
        <>
            <div
                className={cn(
                    'fixed inset-0 z-40 bg-shell-overlay/35 transition-opacity lg:hidden',
                    open
                        ? 'pointer-events-auto opacity-100'
                        : 'pointer-events-none opacity-0',
                )}
                aria-hidden="true"
                onClick={onClose}
            />
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-shell-sidebar text-shell-sidebar-foreground shadow-xl transition-transform lg:translate-x-0',
                    open ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                <div className="flex min-h-32 flex-col items-center justify-center gap-2 border-b border-shell-sidebar-foreground/12 px-6">
                    <Link href={dashboard.url()} onClick={onClose}>
                        <div className="flex size-16 items-center justify-center rounded-full bg-shell-brand text-2xl font-extrabold text-shell-brand-foreground shadow-sm">
                            E
                        </div>
                    </Link>
                    <span className="text-xs font-semibold tracking-wide text-shell-sidebar-muted/70 uppercase">
                        {roleCaption}
                    </span>
                </div>

                <div className="flex-1 px-4 py-5">
                    <nav className="space-y-1" aria-label={navAriaLabel}>
                        {navItems.map((item) => (
                            <SidebarNavLink
                                key={item.label}
                                item={item}
                                active={isActiveNavItem(url, item)}
                                onNavigate={onClose}
                                real={useRealLinks}
                            />
                        ))}
                    </nav>
                </div>

                <div className="border-t border-shell-sidebar-foreground/20 bg-shell-overlay/10 px-4 py-5">
                    <div className="px-1">{footer}</div>
                    <Link
                        href={logout()}
                        as="button"
                        className="mt-4 flex w-full items-center justify-center gap-2 rounded-md bg-shell-sidebar-foreground/12 px-3 py-2 text-sm font-semibold text-shell-sidebar-foreground transition-colors hover:bg-shell-sidebar-foreground/20"
                    >
                        <LogOutIcon className="size-4" />
                        Salir
                    </Link>
                </div>
            </aside>
        </>
    );
}

export function Breadcrumbs({ items }: { items: DisplayBreadcrumb[] }) {
    return (
        <nav aria-label="Breadcrumb" className="min-w-0">
            <ol className="flex min-w-0 items-center gap-1.5 text-sm">
                {items.map((breadcrumb, index) => {
                    const isLast = index === items.length - 1;

                    return (
                        <li
                            key={`${breadcrumb.title}-${index}`}
                            className="flex min-w-0 items-center gap-1.5"
                        >
                            {index > 0 && (
                                <ChevronRightIcon className="size-4 shrink-0 text-shell-subtle-foreground" />
                            )}
                            {isLast ? (
                                <span
                                    className={cn(
                                        'truncate font-medium text-shell-secondary-foreground',
                                        index === 0 &&
                                            'text-base font-semibold text-shell-heading sm:text-lg',
                                    )}
                                >
                                    {breadcrumb.title}
                                </span>
                            ) : (
                                <Link
                                    href={breadcrumb.href}
                                    className={cn(
                                        'truncate font-medium text-shell-muted-foreground transition-colors hover:text-primary',
                                        index === 0 &&
                                            'text-base font-semibold text-shell-heading sm:text-lg',
                                    )}
                                >
                                    {breadcrumb.title}
                                </Link>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}

export function RoleLayoutFrame({
    sidebarOpen,
    onToggleSidebar,
    breadcrumbs,
    headerExtra,
    userInitials,
    user,
    contentWidth = 'full',
    children,
}: {
    sidebarOpen: boolean;
    onToggleSidebar: () => void;
    breadcrumbs: DisplayBreadcrumb[];
    headerExtra?: ReactNode;
    userInitials: string;
    user: User;
    contentWidth?: 'full' | 'contained';
    children: ReactNode;
}) {
    return (
        <div className="min-h-screen bg-shell-frame pt-2 lg:pl-64">
            <div className="min-h-[calc(100vh-0.75rem)] overflow-hidden rounded-tl-lg bg-shell-canvas shadow-shell max-lg:rounded-t-lg">
                <header className="sticky top-0 z-30 border-b border-shell-border bg-shell-surface/95 shadow-sm backdrop-blur">
                    <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                aria-label={
                                    sidebarOpen
                                        ? 'Cerrar navegación'
                                        : 'Abrir navegación'
                                }
                                className="inline-flex size-10 items-center justify-center rounded-md text-shell-foreground transition-colors hover:bg-shell-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-primary lg:hidden"
                                onClick={onToggleSidebar}
                            >
                                {sidebarOpen ? (
                                    <XIcon className="size-5" />
                                ) : (
                                    <MenuIcon className="size-5" />
                                )}
                            </button>
                            {headerExtra}
                            <Breadcrumbs items={breadcrumbs} />
                        </div>

                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                aria-label="Notificaciones (próximamente)"
                                className="inline-flex size-10 items-center justify-center rounded-md text-shell-foreground transition-colors hover:bg-shell-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                            >
                                <BellIcon className="size-5" />
                            </button>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <button
                                        type="button"
                                        className="flex size-10 items-center justify-center rounded-full bg-shell-control text-xs font-semibold tracking-wider text-shell-control-foreground uppercase transition-colors hover:bg-shell-control-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                    >
                                        {userInitials}
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    className="w-56"
                                >
                                    <UserMenuContent user={user} />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </header>

                <main
                    className={cn(
                        'w-full px-4 py-8 sm:px-6 lg:px-10',
                        contentWidth === 'contained' && 'mx-auto max-w-7xl',
                    )}
                >
                    {children}
                </main>
            </div>
        </div>
    );
}
