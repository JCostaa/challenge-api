<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'name',
        'user_id',
        'restrictions',
        'permissions',
        'allowed_ips',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'allowed_ips' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Gera uma nova API key
     *
     * @return string
     */
    public static function generateKey(): string
    {
        // Gerar uma chave aleatória com prefixo diferenciado para facilitar a identificação
        return 'jctech_' . bin2hex(random_bytes(24));
    }

    /**
     * Relationship with the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se a API Key está válida
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->trashed() && 
               ($this->expires_at === null || $this->expires_at > now());
    }

    /**
     * Verifica se a API Key pode ser usada no IP atual
     *
     * @param string|null $ip
     * @return bool
     */
    public function canBeUsedFromIp(?string $ip = null): bool
    {
        if (empty($this->allowed_ips)) {
            return true;
        }

        $ip = $ip ?: request()->ip();
        return in_array($ip, $this->allowed_ips);
    }

    /**
     * Verifica se a API Key tem uma permissão específica
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // Sem restrições de permissão = acesso total
        }

        return in_array($permission, $this->permissions) || 
               in_array('*', $this->permissions);
    }

    /**
     * Atualiza o timestamp de último uso
     *
     * @return void
     */
    public function markAsUsed(): void
    {
        $this->timestamps = false; // Não atualizar updated_at
        $this->update(['last_used_at' => now()]);
        $this->timestamps = true;
    }
}
