<?php

namespace App\Http\Requests\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class GuardarCatalogoConNivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $modelo = $this->modeloRuta();

        return $modelo instanceof Model
            ? $this->user()?->can('update', $modelo) === true
            : $this->user()?->can('create', $this->claseModelo()) === true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $modelo = $this->modeloRuta();

        return [
            'nivel_id' => [
                'required',
                'integer',
                Rule::exists('niveles', 'id')->where('activo', true),
            ],
            'descripcion' => [
                'required',
                'string',
                'max:64',
                Rule::unique($this->tabla(), 'descripcion')
                    ->where('nivel_id', $this->integer('nivel_id'))
                    ->ignore($modelo),
            ],
            'orden' => ['required', 'integer', 'between:1,255'],
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $modelo = $this->modeloRuta();

            if ($modelo instanceof Model
                && (int) $modelo->getAttribute('nivel_id') !== $this->integer('nivel_id')
                && method_exists($modelo, 'grupos')
                && $modelo->grupos()->exists()) {
                $validator->errors()->add(
                    'nivel_id',
                    'El nivel no puede cambiarse porque el catálogo ya está utilizado por grupos.',
                );
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'descripcion' => $this->string('descripcion')->trim()->toString(),
        ]);
    }

    abstract protected function tabla(): string;

    /** @return class-string<Model> */
    abstract protected function claseModelo(): string;

    abstract protected function parametroRuta(): string;

    protected function modeloRuta(): ?Model
    {
        $modelo = $this->route($this->parametroRuta());

        return $modelo instanceof Model ? $modelo : null;
    }
}
