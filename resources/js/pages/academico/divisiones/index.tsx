import DivisionController from '@/actions/App/Http/Controllers/Academico/DivisionController';
import CatalogoSimpleIndex from '@/components/catalogo-simple/CatalogoSimpleIndex';
import type { CatalogoSimple } from '@/components/catalogo-simple/types';
import { index } from '@/routes/academico/divisiones';

export default function DivisionesIndex({
    divisiones,
}: {
    divisiones: CatalogoSimple[];
}) {
    return (
        <CatalogoSimpleIndex
            titulo="Divisiones"
            descripcion="A, B, C y demás divisiones"
            entidad="divisiones"
            nuevoLabel="Nueva división"
            data={divisiones}
            controller={DivisionController}
        />
    );
}

DivisionesIndex.layout = {
    breadcrumbs: [{ title: 'Divisiones', href: index() }],
};
