import DivisionController from '@/actions/App/Http/Controllers/Academico/DivisionController';
import CatalogoSimpleForm from '@/components/catalogo-simple/CatalogoSimpleForm';
import type { CatalogoSimple } from '@/components/catalogo-simple/types';
import { index } from '@/routes/academico/divisiones';

export default function DivisionForm({
    division,
}: {
    division?: CatalogoSimple;
}) {
    return (
        <CatalogoSimpleForm
            entidad="división"
            controller={DivisionController}
            item={division}
        />
    );
}

DivisionForm.layout = {
    breadcrumbs: [
        { title: 'Divisiones', href: index() },
        { title: 'Formulario', href: index() },
    ],
};
