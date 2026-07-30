import TurnoController from '@/actions/App/Http/Controllers/Academico/TurnoController';
import CatalogoSimpleIndex from '@/components/catalogo-simple/CatalogoSimpleIndex';
import type { CatalogoSimple } from '@/components/catalogo-simple/types';
import { index } from '@/routes/academico/turnos';

export default function TurnosIndex({ turnos }: { turnos: CatalogoSimple[] }) {
    return (
        <CatalogoSimpleIndex
            titulo="Turnos"
            descripcion="Mañana, Tarde, Única y demás turnos"
            entidad="turnos"
            nuevoLabel="Nuevo turno"
            data={turnos}
            controller={TurnoController}
        />
    );
}

TurnosIndex.layout = {
    breadcrumbs: [{ title: 'Turnos', href: index() }],
};
