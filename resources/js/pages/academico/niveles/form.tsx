import NivelController from '@/actions/App/Http/Controllers/Academico/NivelController';
import CatalogoSimpleForm from '@/components/catalogo-simple/CatalogoSimpleForm';
import type { CatalogoSimple } from '@/components/catalogo-simple/types';
import { index } from '@/routes/academico/niveles';

export default function NivelForm({ nivel }: { nivel?: CatalogoSimple }) {
    return (
        <CatalogoSimpleForm
            entidad="nivel"
            controller={NivelController}
            item={nivel}
        />
    );
}

NivelForm.layout = {
    breadcrumbs: [
        { title: 'Niveles', href: index() },
        { title: 'Formulario', href: index() },
    ],
};
