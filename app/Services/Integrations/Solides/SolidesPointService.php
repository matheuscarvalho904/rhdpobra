<?php

namespace App\Services\Integrations\Solides;

use App\Models\PointIntegration;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SolidesPointService
{
    public function __construct(
        protected PointIntegration $integration
    ) {}

    protected function client(): PendingRequest
    {
        $token = trim((string) $this->integration->token);

        // Limpeza de token (evita erro com "Authorization: Basic ...")
        $token = preg_replace('/\s+/', ' ', $token);
        $token = str_replace('Authorization:', '', $token);
        $token = trim($token);

        $authorization = str_starts_with($token, 'Basic ')
            ? $token
            : 'Basic ' . $token;

        return Http::withoutVerifying() // LOCAL (remover em produção)
            ->timeout(30)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => 'application/json',
            ]);
    }

    protected function baseUrl(): string
    {
        return rtrim(
            $this->integration->base_url ?: 'https://api.tangerino.com.br/api/punch',
            '/'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEST CONNECTION
    |--------------------------------------------------------------------------
    */
    public function testConnection(): array
    {
        try {
            if (! $this->integration->active) {
                return $this->fail('A integração está inativa.');
            }

            if (blank($this->integration->token)) {
                return $this->fail('O token de integração não foi informado.');
            }

            $response = $this->client()
                ->get('https://employer.tangerino.com.br/test');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexão realizada com sucesso.',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }

            return $this->responseError($response, 'Erro ao testar conexão.');
        } catch (\Throwable $e) {
            Log::error('Erro testConnection', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
            ]);

            return $this->fail($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET PUNCHES (CORRETO)
    |--------------------------------------------------------------------------
    */
    public function getPunches(array $params = []): array
    {
        try {
            $response = $this->client()
                ->get($this->baseUrl(), $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Marcações consultadas com sucesso.',
                    'status' => $response->status(),
                    'data' => $this->safeJson($response),
                    'body' => $response->body(),
                ];
            }

            return $this->responseError($response, 'Erro ao buscar marcações.');
        } catch (\Throwable $e) {
            Log::error('Erro getPunches', [
                'integration_id' => $this->integration->id,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return $this->fail($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET PUNCHES BY PERIOD
    |--------------------------------------------------------------------------
    */
    public function getPunchesByPeriod(
        string $startDate,
        string $endDate,
        ?string $employeeId = null,
        array $extraParams = []
    ): array {
        $params = array_merge($extraParams, [
            'startDate' => $this->dateToMilliseconds($startDate),
            'endDate' => $this->dateToMilliseconds($endDate),
        ]);

        if ($employeeId) {
            $params['employeeId'] = $employeeId;
        }

        return $this->getPunches($params);
    }

    /*
    |--------------------------------------------------------------------------
    | TIME SHEET (PDF)
    |--------------------------------------------------------------------------
    */
    public function getTimeSheetPdf(array $params = []): array
    {
        try {
            $response = $this->client()
                ->get($this->baseUrl() . '/time-sheet', $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Time-sheet consultado com sucesso.',
                    'status' => $response->status(),
                    'data' => $this->safeJson($response),
                ];
            }

            return $this->responseError($response, 'Erro ao buscar time-sheet.');
        } catch (\Throwable $e) {
            Log::error('Erro getTimeSheetPdf', [
                'integration_id' => $this->integration->id,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return $this->fail($e->getMessage());
        }
    }

    public function getTimeSheetPdfByPeriod(
        string $startDate,
        string $endDate,
        ?string $employeeId = null,
        array $extraParams = []
    ): array {
        $params = array_merge($extraParams, [
            'startDate' => $this->dateToMilliseconds($startDate),
            'endDate' => $this->dateToMilliseconds($endDate),
        ]);

        if ($employeeId) {
            $params['employeeId'] = $employeeId;
        }

        return $this->getTimeSheetPdf($params);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function dateToMilliseconds(string $date): int
    {
        return Carbon::parse($date)->startOfDay()->timestamp * 1000;
    }

    protected function safeJson(Response $response): mixed
    {
        $body = $response->body();

        if (blank($body)) {
            return null;
        }

        $json = json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE ? $json : null;
    }

    protected function responseError(Response $response, string $defaultMessage): array
    {
        return [
            'success' => false,
            'message' => match ($response->status()) {
                401 => 'Token inválido ou sem permissão.',
                403 => 'Acesso negado pela API.',
                404 => 'Endpoint não encontrado.',
                default => $defaultMessage,
            },
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    protected function fail(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}