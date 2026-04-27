<?php

namespace App\Services\Integrations\Solides;

use App\Models\PointIntegration;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SolidesPointService
{
    public function __construct(
        protected PointIntegration $integration
    ) {}

    protected function client(): PendingRequest
    {
        return Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Basic ' . $this->integration->token,
                'Content-Type' => 'application/json',
            ]);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) $this->integration->base_url, '/');
    }

    public function testConnection(): array
    {
        try {
            if (! $this->integration->active) {
                return [
                    'success' => false,
                    'message' => 'A integração está inativa.',
                ];
            }

            if (blank($this->integration->base_url)) {
                return [
                    'success' => false,
                    'message' => 'A URL base da API não foi informada.',
                ];
            }

            if (blank($this->integration->token)) {
                return [
                    'success' => false,
                    'message' => 'O token de integração não foi informado.',
                ];
            }

            /*
             * Endpoint inicial para teste.
             * Pode ser ajustado conforme o endpoint liberado pela Sólides.
             */
            $response = $this->client()
                ->get($this->baseUrl());

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexão realizada com sucesso.',
                    'status' => $response->status(),
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'A API respondeu com erro.',
                'status' => $response->status(),
                'body' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('Erro ao testar conexão com Sólides Ponto', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao conectar com a API: ' . $e->getMessage(),
            ];
        }
    }

    public function getEmployees(array $params = []): array
    {
        try {
            $response = $this->client()
                ->get($this->baseUrl() . '/employees', $params);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao buscar colaboradores na Sólides.',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }

            return [
                'success' => true,
                'data' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar colaboradores Sólides', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getTimeEntries(string $startDate, string $endDate, array $params = []): array
    {
        try {
            $payload = array_merge($params, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $response = $this->client()
                ->get($this->baseUrl() . '/time-entries', $payload);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao buscar marcações de ponto na Sólides.',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }

            return [
                'success' => true,
                'data' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar marcações Sólides', [
                'integration_id' => $this->integration->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getTimeSheetPdf(string $startDate, string $endDate, array $params = []): array
    {
        try {
            $payload = array_merge($params, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $response = $this->client()
                ->get($this->baseUrl() . '/time-sheet', $payload);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao buscar espelho de ponto PDF na Sólides.',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }

            return [
                'success' => true,
                'data' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar PDF de ponto Sólides', [
                'integration_id' => $this->integration->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}