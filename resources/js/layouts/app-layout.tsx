import AdminGeneralLayout from '@/layouts/admin-general-layout';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    return (
        <AdminGeneralLayout breadcrumbs={breadcrumbs}>
            {children}
        </AdminGeneralLayout>
    );
}
