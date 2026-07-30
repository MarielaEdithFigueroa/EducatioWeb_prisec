<?php

namespace App\Models;

use Database\Factories\LogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['entidad', 'entidad_id', 'accion', 'usuario_id', 'login', 'session_id', 'anterior', 'nuevo', 'ip', 'user_agent', 'origen'])]
class Log extends Model
{
    /** @use HasFactory<LogFactory> */
    use HasFactory;

    protected $table = 'logs';

    const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anterior' => 'array',
            'nuevo' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
