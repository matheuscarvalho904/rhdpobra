<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CepService
{
    public function search(string $cep): ?array
    {
        $cep = preg_replace('/\D+/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        $response = Http::timeout(10)->get("https://viacep.com.br/ws/{$cep}/json/");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data) || ($data['erro'] ?? false)) {
            return null;
        }

        return [
            'zip_code' => $data['cep'] ?? null,
            'address' => $data['logradouro'] ?? null,
            'complement' => $data['complemento'] ?? null,
            'district' => $data['bairro'] ?? null,
            'city' => $data['localidade'] ?? null,
            'state' => $data['uf'] ?? null,
        ];
    }
}