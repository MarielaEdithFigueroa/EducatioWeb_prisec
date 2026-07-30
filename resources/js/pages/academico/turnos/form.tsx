import TurnoController from '@/actions/App/Http/Controllers/Academico/TurnoController';
import CatalogoSimpleForm from '@/components/catalogo-simple/CatalogoSimpleForm';
import type { CatalogoSimple } from '@/components/catalogo-simple/types';
import { index } from '@/routes/academico/turnos';

export default function TurnoForm({ turno }: { turno?: CatalogoSimple }) {
    return (
        <CatalogoSimpleForm
            entidad="turno"
            controller={TurnoController}
            item={turno}
        />
    );
}

TurnoForm.layout = {
    breadcrumbs: [
        { title: 'Turnos', href: index() },
        { title: 'Formulario', href: index() },
    ],
};
