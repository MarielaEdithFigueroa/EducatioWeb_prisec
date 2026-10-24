import { useMemo } from 'react';
import { Checkbox } from '@/components/ui/checkbox';
import EureDatePickerGroup from '@/components/ui/eure/EureDatePickerGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import { Label } from '@/components/ui/label';
import { parseDateOnly, toDateOnlyString } from '@/lib/date';
import type { DatosVinculo, TipoVinculo } from './types';

type Props = {
    value: DatosVinculo;
    onChange: (value: DatosVinculo) => void;
    tiposVinculo: TipoVinculo[];
    errors?: Record<string, string | undefined>;
    prefijoError?: string;
};

const grupos = [
    {
        titulo: 'Roles',
        opciones: [
            ['es_responsable_legal', 'Responsable legal'],
            ['es_responsable_pedagogico', 'Responsable pedagógico'],
            ['es_responsable_economico', 'Responsable económico'],
        ],
    },
    {
        titulo: 'Recepción',
        opciones: [
            ['recibe_comunicaciones', 'Recibe comunicaciones'],
            ['recibe_informes_academicos', 'Recibe informes académicos'],
            ['recibe_facturacion', 'Recibe facturación'],
        ],
    },
    {
        titulo: 'Operativa',
        opciones: [
            ['es_contacto_principal', 'Contacto principal'],
            ['es_contacto_emergencia', 'Contacto de emergencia'],
            ['convive_con_alumno', 'Convive con el alumno'],
            ['autorizado_retirar', 'Autorizado a retirar'],
            ['habilitado_portal', 'Habilitado en el portal'],
        ],
    },
] as const;

export default function FormularioVinculo({
    value,
    onChange,
    tiposVinculo,
    errors = {},
    prefijoError = 'vinculo.',
}: Props) {
    const opcionesVinculo = useMemo<EureSelectOption[]>(
        () =>
            tiposVinculo.map((tipo) => ({
                value: tipo.id,
                label: tipo.nombre,
            })),
        [tiposVinculo],
    );

    const cambiar = <K extends keyof DatosVinculo>(
        campo: K,
        nuevoValor: DatosVinculo[K],
    ) => onChange({ ...value, [campo]: nuevoValor });

    return (
        <div className="space-y-4">
            <div className="grid gap-3 md:grid-cols-3">
                <EureSelectGroup
                    label="Vínculo"
                    name="vinculo_id"
                    value={
                        opcionesVinculo.find(
                            (opcion) => opcion.value === value.vinculo_id,
                        ) ?? null
                    }
                    onChange={(opcion) =>
                        cambiar(
                            'vinculo_id',
                            opcion === null ? '' : Number(opcion.value),
                        )
                    }
                    options={opcionesVinculo}
                    placeholder="Buscar vínculo"
                    error={Boolean(errors[`${prefijoError}vinculo_id`])}
                    errorMessage={errors[`${prefijoError}vinculo_id`]}
                    required
                />
                <EureDatePickerGroup
                    label="Desde"
                    name="fecha_desde"
                    value={parseDateOnly(value.fecha_desde)}
                    onChange={(fecha) =>
                        cambiar('fecha_desde', toDateOnlyString(fecha))
                    }
                    error={Boolean(errors[`${prefijoError}fecha_desde`])}
                    errorMessage={errors[`${prefijoError}fecha_desde`]}
                />
                <EureDatePickerGroup
                    label="Hasta"
                    name="fecha_hasta"
                    value={parseDateOnly(value.fecha_hasta)}
                    onChange={(fecha) =>
                        cambiar('fecha_hasta', toDateOnlyString(fecha))
                    }
                    error={Boolean(errors[`${prefijoError}fecha_hasta`])}
                    errorMessage={errors[`${prefijoError}fecha_hasta`]}
                />
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                {grupos.map((grupo) => (
                    <fieldset key={grupo.titulo} className="space-y-3">
                        <legend className="text-sm font-semibold text-foreground">
                            {grupo.titulo}
                        </legend>
                        {grupo.opciones.map(([campo, etiqueta]) => (
                            <div key={campo} className="flex items-start gap-2">
                                <Checkbox
                                    id={`vinculo-${campo}`}
                                    checked={value[campo]}
                                    onCheckedChange={(checked) =>
                                        cambiar(campo, checked === true)
                                    }
                                />
                                <Label
                                    htmlFor={`vinculo-${campo}`}
                                    className="cursor-pointer leading-4"
                                >
                                    {etiqueta}
                                </Label>
                            </div>
                        ))}
                    </fieldset>
                ))}
            </div>

            <EureTextareaGroup
                label="Observaciones del vínculo"
                name="observaciones_vinculo"
                value={value.observaciones}
                onChange={(event) =>
                    cambiar('observaciones', event.target.value)
                }
                error={Boolean(errors[`${prefijoError}observaciones`])}
                errorMessage={errors[`${prefijoError}observaciones`]}
                maxLength={5000}
                rows={3}
            />
        </div>
    );
}
