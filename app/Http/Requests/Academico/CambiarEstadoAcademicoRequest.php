<?php

namespace App\Http\Requests\Academico;

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use App\Models\Grupo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CambiarEstadoAcademicoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $modelo = $this->modelo();
        $habilidad = $this->routeIs('*.reactivar') ? 'reactivate' : 'deactivate';

        return $modelo instanceof Model && $this->user()?->can($habilidad, $modelo) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $modelo = $this->modelo();

            if ($modelo instanceof AnioLectivo
                && ! $this->routeIs('*.reactivar')
                && $modelo->estado === EstadoAnioLectivo::Vigente) {
                $validator->errors()->add('anio', 'El año lectivo vigente no se puede desactivar.');
            }

            if ($modelo instanceof Model
                && ! $modelo instanceof Grupo
                && ! $this->routeIs('*.reactivar')
                && method_exists($modelo, 'grupos')
                && $modelo->grupos()->activos()->exists()) {
                $validator->errors()->add(
                    'activo',
                    'No se puede desactivar porque el registro está utilizado por grupos activos.',
                );
            }

            if ($modelo instanceof Grupo && $this->routeIs('*.reactivar')) {
                $modelo->loadMissing(['anioLectivo', 'planEstudio', 'curso', 'division', 'turno']);
                $relacionesActivas = $modelo->anioLectivo->activo
                    && $modelo->planEstudio->activo
                    && $modelo->curso->activo
                    && $modelo->division->activo
                    && $modelo->turno->activo;
                $niveles = collect([
                    $modelo->planEstudio->nivel_id,
                    $modelo->curso->nivel_id,
                    $modelo->division->nivel_id,
                    $modelo->turno->nivel_id,
                ])->unique();

                if (! $relacionesActivas
                    || $modelo->anioLectivo->estado === EstadoAnioLectivo::Cerrado
                    || $niveles->count() !== 1) {
                    $validator->errors()->add(
                        'activo',
                        'El grupo no puede reactivarse porque alguna relación está inactiva, el año está cerrado o los niveles no coinciden.',
                    );
                }
            }
        }];
    }

    private function modelo(): ?Model
    {
        $route = $this->route();

        if (! is_object($route) || ! method_exists($route, 'parameters')) {
            return null;
        }

        return collect($route->parameters())
            ->first(fn (mixed $parametro): bool => $parametro instanceof Model);
    }
}
