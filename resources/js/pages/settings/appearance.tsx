import { Head } from '@inertiajs/react';
import { PaletteIcon } from 'lucide-react';
import AppearanceTabs from '@/components/appearance-tabs';
import EducatioCard from '@/components/educatio-card';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Apariencia" />
            <EducatioCard
                title="Apariencia"
                icon={PaletteIcon}
                description="Elegí el modo de color que te resulte más cómodo para trabajar."
            >
                <AppearanceTabs />
                <p className="mt-3 text-sm text-muted-foreground">
                    La opción Sistema sigue la configuración de tu dispositivo.
                </p>
            </EducatioCard>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [{ title: 'Apariencia', href: editAppearance() }],
};
