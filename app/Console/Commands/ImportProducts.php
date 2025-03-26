<?php

namespace App\Console\Commands;

use App\Events\SyncFailedEvent;
use App\Models\Enum\ProductStatus;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import {--chunk=100 : Tamanho dos lotes de processamento}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from external API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('STARTING IMPORT PRODUCTS PROCESS');
        $successFiles = 0;
        $failedFiles = 0;
        $startTime = microtime(true);

        try {
            // Obter tamanho do chunk da opção de comando
            $chunkSize = (int)$this->option('chunk');
            if ($chunkSize <= 0) {
                $chunkSize = 100;
            }
            
            $this->info("Usando tamanho de lote: {$chunkSize}");

            // Configuração inicial
            $client = new Client([
                'timeout' => 60,  // Aumento do timeout para 60 segundos
                'connect_timeout' => 10,
            ]);
            $productListUrl = env('PRODUCT_LIST_URL');
            $baseUrl = env('PRODUCT_IMPORT_BASE_URL');

            // Validar URLs
            if (empty($productListUrl) || !is_string($productListUrl)) {
                throw new \Exception("PRODUCT_LIST_URL não está configurada corretamente no arquivo .env");
            }

            if (empty($baseUrl) || !is_string($baseUrl)) {
                throw new \Exception("PRODUCT_IMPORT_BASE_URL não está configurada corretamente no arquivo .env");
            }

            // Garantir que a URL base termine com '/'
            if (!str_ends_with($baseUrl, '/')) {
                $baseUrl .= '/';
            }

            // Obter a lista de produtos
            $this->info("Obtendo lista de produtos de {$productListUrl}...");
            $response = $client->get($productListUrl);
            $productsList = explode("\n", $response->getBody()->getContents());
            
            // Remover linha vazia no final se existir
            if (empty(end($productsList))) {
                array_pop($productsList);
            }

            $totalProducts = count($productsList);
            $this->info("Encontrados {$totalProducts} arquivos para importar");

            // Processar cada arquivo de produto
            foreach ($productsList as $index => $productFile) {
                $currentNumber = $index + 1;
                $fileStartTime = microtime(true);
                $this->info("Processando arquivo [{$currentNumber}/{$totalProducts}]: {$productFile}");
                
                try {
                    $productFile = trim($productFile);
                    if (empty($productFile)) {
                        continue;
                    }
                    
                    $productUrl = $baseUrl . $productFile;
                    $this->info("Baixando arquivo de {$productUrl}...");
                    $response = $client->get($productUrl, [
                        'sink' => storage_path("app/public/{$productFile}")
                    ]);

                    if ($response->getStatusCode() === Response::HTTP_OK) {
                        $filePath = storage_path("app/public/{$productFile}");
                        $this->info("Arquivo salvo em {$filePath}. Iniciando processamento...");
                        
                        // Processar o arquivo em chunks para economizar memória
                        $productsImported = $this->processFileInChunks($filePath, $chunkSize);
                        
                        // Limpar referências para liberar memória
                        $response = null;
                        
                        // Forçar coleta de lixo
                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }
                        
                        $fileEndTime = microtime(true);
                        $fileElapsedTime = round($fileEndTime - $fileStartTime, 2);
                        $this->info("Arquivo {$productFile} processado com sucesso em {$fileElapsedTime}s. {$productsImported} produtos importados.");
                        $successFiles++;
                    }
                } catch (\Exception $e) {
                    $failedFiles++;
                    $this->error("Falha ao processar arquivo {$productFile}: " . $e->getMessage());
                    \Log::error("Falha ao processar arquivo {$productFile}: " . $e->getMessage());
                    // Continuar com o próximo arquivo em vez de encerrar completamente
                }
            }

            $endTime = microtime(true);
            $elapsedTime = round($endTime - $startTime, 2);
            $this->info("Importação concluída em {$elapsedTime}s: {$successFiles} arquivos processados com sucesso, {$failedFiles} falhas");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            \Log::error('Falha na importação: ' . $e->getMessage());
            $this->error('Falha na importação: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Processa um arquivo em chunks para economizar memória
     */
    private function processFileInChunks($filePath, $batchSize = 100)
    {
        $this->info("Processando arquivo {$filePath} em lotes de {$batchSize}");
        
        // Verificar se é um arquivo .gz
        $isGzFile = pathinfo($filePath, PATHINFO_EXTENSION) === 'gz';
        
        if ($isGzFile) {
            $this->info("Detectado arquivo compactado gzip");
            $handle = gzopen($filePath, 'r');
            if ($handle === false) {
                throw new \Exception("Não foi possível abrir o arquivo gzip: {$filePath}");
            }
        } else {
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \Exception("Não foi possível abrir o arquivo: {$filePath}");
            }
        }

        $batch = [];
        $lineCount = 0;
        $processedCount = 0;
        $successCount = 0;
        $failCount = 0;
        $lastLogTime = microtime(true);
        $startTime = microtime(true);
        
        try {
            $this->output->progressStart(); // Iniciar barra de progresso
            
            $readFunction = $isGzFile ? 'gzgets' : 'fgets';
            
            while (!($isGzFile ? gzeof($handle) : feof($handle))) {
                $line = $readFunction($handle);
                
                if ($line !== false) {
                    $lineCount++;
                    
                    // Mostrar progresso a cada 1 segundo
                    $now = microtime(true);
                    if ($now - $lastLogTime >= 1) {
                        $this->output->progressAdvance();
                        $lastLogTime = $now;
                    }
                    
                    try {
                        $lineData = json_decode($line, true);
                        
                        if ($lineData) {
                            $batch[] = $lineData;
                            $processedCount++;
                            
                            // Processar o lote quando atingir o tamanho desejado
                            if (count($batch) >= $batchSize) {
                                $this->info("Processando lote de {$batchSize} produtos (linha {$lineCount})...");
                                $batchStartTime = microtime(true);
                                
                                $batchResults = $this->storeProductsBatch($batch);
                                $successCount += $batchResults;
                                $failCount += count($batch) - $batchResults;
                                
                                $batchEndTime = microtime(true);
                                $batchElapsedTime = round($batchEndTime - $batchStartTime, 2);
                                $this->info("Lote processado em {$batchElapsedTime}s: {$batchResults} sucessos, " . (count($batch) - $batchResults) . " falhas");
                                
                                $batch = []; // Limpar lote
                                
                                // Forçar coleta de lixo
                                if (function_exists('gc_collect_cycles')) {
                                    gc_collect_cycles();
                                }
                            }
                        } else {
                            if (trim($line) !== '') {
                                $this->warn("Linha {$lineCount}: JSON inválido");
                                $failCount++;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->warn("Erro ao processar linha {$lineCount}: " . $e->getMessage());
                        $failCount++;
                    }
                }
            }
            
            $this->output->progressFinish(); // Finalizar barra de progresso
            
            // Processar o último lote (caso exista)
            if (!empty($batch)) {
                $this->info("Processando lote final com " . count($batch) . " produtos...");
                $batchStartTime = microtime(true);
                
                $batchResults = $this->storeProductsBatch($batch);
                $successCount += $batchResults;
                $failCount += count($batch) - $batchResults;
                
                $batchEndTime = microtime(true);
                $batchElapsedTime = round($batchEndTime - $batchStartTime, 2);
                $this->info("Lote final processado em {$batchElapsedTime}s: {$batchResults} sucessos, " . (count($batch) - $batchResults) . " falhas");
            }
        } finally {
            // Garantimos que o arquivo seja fechado mesmo em caso de exceção
            if ($isGzFile) {
                gzclose($handle);
            } else {
                fclose($handle);
            }
            
            // Remover o arquivo temporário para liberar espaço
            unlink($filePath);
        }
        
        $endTime = microtime(true);
        $elapsedTime = round($endTime - $startTime, 2);
        $this->info("Processamento concluído em {$elapsedTime}s: {$lineCount} linhas lidas, {$processedCount} processadas, {$successCount} produtos salvos, {$failCount} falhas");
        
        return $successCount;
    }

    /**
     * Armazena um lote de produtos no banco de dados e indexa no Algolia
     */
    private function storeProductsBatch(array $productBatch)
    {
        $products = collect();
        
        // Primeiro, salvar todos os produtos no banco
        foreach ($productBatch as $productData) {
            try {
                DB::beginTransaction();
                
                $product = $this->saveProduct($productData);
                $products->push($product);
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->warn("Erro ao salvar produto " . ($productData['code'] ?? 'desconhecido') . ": " . $e->getMessage());
            }
        }
        
        // Se temos produtos para indexar e o Algolia está configurado
        $successCount = $products->count();
        if ($successCount > 0) {
            $this->indexProductsToAlgolia($products);
        }
        
        return $successCount;
    }

    /**
     * Salva um único produto no banco de dados
     * @return Product O modelo do produto salvo
     */
    private function saveProduct(array $productData)
    {
        // Primeiro, valida os dados essenciais
        if (empty($productData['code'])) {
            throw new \Exception("Código do produto ausente");
        }
        
        // Correção para o problema de aspas nos códigos
        $code = trim($productData['code'], '"');
        
        // Formatação do código como no método storeProducts original
        $formatedCode = (int) preg_replace('/^"*(0*)/', '', $code);

        return Product::updateOrCreate(
            [
                'code' => $formatedCode,
            ],
            [
                'status' => ProductStatus::Published,
                'imported_t' => now(),
                'url' => $productData['url'] ?? null,
                'creator' => $productData['creator'] ?? null,
                'created_t' => $productData['created_t'] ?? null,
                'last_modified_t' => $productData['last_modified_t'] ?? null,
                'product_name' => $productData['product_name'] ?? null,
                'quantity' => $productData['quantity'] ?? null,
                'brands' => $productData['brands'] ?? null,
                'categories' => $productData['categories'] ?? null,
                'labels' => $productData['labels'] ?? null,
                'cities' => $productData['cities'] ?? null,
                'purchase_places' => $productData['purchase_places'] ?? null,
                'stores' => $productData['stores'] ?? null,
                'ingredients_text' => $productData['ingredients_text'] ?? null,
                'traces' => $productData['traces'] ?? null,
                'serving_size' => $productData['serving_size'] ?? null,
                'serving_quantity' => (!empty($productData['serving_quantity']) ? $productData['serving_quantity'] : null),
                'nutriscore_score' => (!empty($productData['nutriscore_score']) ? $productData['nutriscore_score'] : null),
                'nutriscore_grade' => $productData['nutriscore_grade'] ?? null,
                'main_category' => $productData['main_category'] ?? null,
                'image_url' => $productData['image_url'] ?? null,
            ]
        );
    }

    /**
     * Indexa uma coleção de produtos no Algolia
     *
     * @param \Illuminate\Support\Collection $products
     * @return void
     */
    private function indexProductsToAlgolia($products)
    {
        // Skip se não estiver usando Algolia
        if (config('scout.driver') !== 'algolia') {
            return;
        }
        
        $count = $products->count();
        $this->info("Indexando {$count} produtos no Algolia...");
        
        try {
            // Laravel Scout tem suporte embutido para collections
            $products->searchable();
            $this->info("✓ {$count} produtos indexados com sucesso no Algolia");
        } catch (\Exception $e) {
            $this->warn("⚠ Erro na indexação do Algolia: " . $e->getMessage());
            Log::warning("Falha ao indexar produtos no Algolia", [
                'error' => $e->getMessage(),
                'count' => $count
            ]);
        }
    }
}
