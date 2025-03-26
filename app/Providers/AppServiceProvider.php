<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         // Verifique o valor de 'some_value' na configuração
         $value = config('app.some_value');
        
         // Verifique se o valor não é numérico
         if (!is_numeric($value)) {
             // Log para monitorar
             \Log::warning('Valor de some_value não numérico encontrado em config/app.php', [
                 'value' => $value
             ]);
 
             // Corrija o valor para um valor padrão numérico
             config(['app.some_value' => 60]); // Exemplo de valor padrão numérico
 
             // Ou, se preferir, defina $value para um valor padrão:
             $value = 60;
         }
         
    }
}
