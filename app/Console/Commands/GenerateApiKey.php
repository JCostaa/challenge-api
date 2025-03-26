<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateApiKey extends Command
{
    protected $signature = 'api-key:generate
                           {--name= : Nome para identificar a API key}
                           {--user-id= : ID do usuário que será dono da chave}
                           {--expires= : Data de expiração (dias)}
                           {--permissions=* : Permissões da API key}';
    
    protected $description = 'Gera uma nova API key';

    public function handle()
    {
        // Obter nome da API Key
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('Digite um nome para identificar a API key');
        }
        
        // Obter usuário
        $userId = $this->option('user-id');
        if (!$userId) {
            $userId = $this->askWithCompletion(
                'Digite o ID do usuário (opcional, pressione enter para pular)',
                User::pluck('id')->toArray()
            );
        }
        
        // Se temos um user ID, verificar se o usuário existe
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuário com ID {$userId} não encontrado");
                return Command::FAILURE;
            }
        }
        
        // Obter data de expiração
        $expiresInDays = $this->option('expires');
        $expiresAt = null;
        
        if ($expiresInDays) {
            $expiresAt = now()->addDays($expiresInDays);
        } elseif ($this->confirm('Deseja definir uma data de expiração para esta API key?', false)) {
            $expiresInDays = $this->ask('Em quantos dias a API key deve expirar?', 30);
            $expiresAt = now()->addDays($expiresInDays);
        }
        
        // Obter permissões
        $permissions = $this->option('permissions');
        if (empty($permissions) && $this->confirm('Deseja adicionar permissões específicas?', false)) {
            $permissions = [];
            $addingPermissions = true;
            
            while ($addingPermissions) {
                $permission = $this->ask('Digite uma permissão (ou deixe em branco para finalizar)');
                
                if (empty($permission)) {
                    $addingPermissions = false;
                } else {
                    $permissions[] = $permission;
                    $this->info("Permissão '{$permission}' adicionada");
                }
            }
        }
        
        // Criar API Key
        $apiKey = ApiKey::create([
            'key' => ApiKey::generateKey(),
            'name' => $name,
            'user_id' => $userId ?: null,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
        ]);
        
        $this->info('API Key gerada com sucesso!');
        $this->newLine();
        
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $apiKey->id],
                ['Chave', $apiKey->key],
                ['Nome', $apiKey->name],
                ['Usuário', $userId ?: 'Nenhum'],
                ['Permissões', empty($permissions) ? 'Todas' : implode(', ', $permissions)],
                ['Expira em', $expiresAt ? $expiresAt->format('d/m/Y H:i') : 'Nunca']
            ]
        );
        
        return Command::SUCCESS;
    }
}
