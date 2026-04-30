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

    // Remove quebra de linha, espaços duplicados e tabulações
    $token = preg_replace('/\s+/', ' ', $token);

    // Se o usuário colou "Authorization: Basic xxx", limpa também
    $token = str_replace('Authorization:', '', $token);
    $token = trim($token);

    // Se já veio com Basic, mantém. Se não veio, adiciona.
    $authorization = str_starts_with($token, 'Basic ')
        ? $token
        : 'Basic ' . $token;

    return Http::withoutVerifying()
        ->timeout(30)
        ->withHeaders([
            'Authorization' => $authorization,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
}
    protected function baseUrl(): string
    {
        return rtrim(
            $this->integration->base_url ?: 'https://employer.tangerino.com.br',
            '/'
        );
    }

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
                ->get($this->baseUrl() . '/test');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexão com Sólides/Tangerino realizada com sucesso.',
                    'status' => $response->status(),
                    'data' => $this->safeJson($response),
                    'body' => $response->body(),
                ];
            }

            return $this->responseError($response, 'A API respondeu com erro.');
        } catch (\Throwable $e) {
            Log::error('Erro ao testar conexão com Sólides/Tangerino', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
            ]);

            return $this->fail('Erro ao conectar com a API: ' . $e->getMessage());
        }
    }

    public function getPunches(array $params = []): array
    {
        try {
            $response = $this->client()
                ->get($this->baseUrl() . '/', $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Marcações consultadas com sucesso.',
                    'status' => $response->status(),
                    'data' => $this->safeJson($response),
                    'body' => $response->body(),
                ];
            }

            return $this->responseError($response, 'Erro ao buscar marcações de ponto.');
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar marcações Sólides/Tangerino', [
                'integration_id' => $this->integration->id,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return $this->fail($e->getMessage());
        }
    }

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

        if (filled($employeeId)) {
            $params['employeeId'] = $employeeId;
        }

        return $this->getPunches($params);
    }

    public function getPunchesByLastUpdate(int $lastUpdateMilliseconds, array $extraParams = []): array
    {
        return $this->getPunches(array_merge($extraParams, [
            'lastUpdate' => $lastUpdateMilliseconds,
        ]));
    }

    public function getTimeSheetPdf(array $params = []): array
    {
        try {
            $response = $this->client()
                ->get($this->baseUrl() . '/time-sheet', $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Folha de ponto consultada com sucesso.',
                    'status' => $response->status(),
                    'data' => $this->safeJson($response),
                    'body' => $response->body(),
                ];
            }

            return $this->responseError($response, 'Erro ao buscar folha de ponto PDF.');
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar folha de ponto PDF Sólides/Tangerino', [
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

        if (filled($employeeId)) {
            $params['employeeId'] = $employeeId;
        }

        return $this->getTimeSheetPdf($params);
    }

    protected function dateToMilliseconds(string $date): int
    {
        return Carbon::parse($date)
            ->startOfDay()
            ->timestamp * 1000;
    }

    protected function responseError(Response $response, string $defaultMessage): array
    {
        $body = $response->body();

        $message = match ($response->status()) {
            401 => 'Não autorizado. Verifique se o token da Sólides/Tangerino está correto.',
            403 => 'Acesso negado. O token não tem permissão para essa API.',
            404 => 'Endpoint não encontrado. Verifique a URL base da integração.',
            500 => 'Erro interno na API da Sólides/Tangerino.',
            default => $defaultMessage,
        };

        return [
            'success' => false,
            'message' => $message,
            'status' => $response->status(),
            'body' => $body,
            'data' => $this->safeJson($response),
        ];
    }

    protected function safeJson(Response $response): mixed
    {
        try {
            return $response->json();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function fail(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}