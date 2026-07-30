import NivelController from '@/actions/App/Http/Controllers/Academico/NivelController';
import CatalogoSimpleIndex from '@/components/catalogo-simple/CatalogoSimpleIndex';
import type { CatalogoSimple } from '@/components/catalogo-simple/types';
import { index } from '@/routes/academico/niveles';

export default function NivelesIndex({
    niveles,
}: {
    niveles: CatalogoSimple[];
}) {
    return (
        <CatalogoSimpleIndex
            titulo="Niveles"
            descripcion="Primaria, Secundaria y demás niveles de la institución"
            entidad="niveles"
            nuevoLabel="Nuevo nivel"
            data={niveles}
            controller={NivelController}
        />
    );
}

NivelesIndex.layout = {
    breadcrumbs: [{ title: 'Niveles', href: index() }],
};
