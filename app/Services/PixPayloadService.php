<?php

namespace App\Services;

use InvalidArgumentException;

class PixPayloadService
{
    public function generatePayload(
        string $pixKey,
        string $pixKeyType,
        string $beneficiaryName,
        string $city,
        float $amount,
        string $txid = 'ADIANTAMENTO'
    ): string {
        $pixKey = $this->normalizePixKey($pixKey, $pixKeyType);

        if ($pixKey === '') {
            throw new InvalidArgumentException('Chave PIX inválida.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('O valor do PIX deve ser maior que zero.');
        }

        $beneficiaryName = $this->normalize($beneficiaryName, 25);
        $city = $this->normalize($city, 15);
        $txid = $this->normalizeTxid($txid, 25);

        if ($beneficiaryName === '') {
            $beneficiaryName = 'FAVORECIDO';
        }

        if ($city === '') {
            $city = 'CIDADE';
        }

        if ($txid === '') {
            $txid = 'ADIANTAMENTO';
        }

        $merchantAccount = $this->buildField(
            '26',
            $this->buildField('00', 'BR.GOV.BCB.PIX') .
            $this->buildField('01', $pixKey)
        );

        $payload =
            '000201' .
            $merchantAccount .
            '52040000' .
            '5303986' .
            $this->buildField('54', number_format($amount, 2, '.', '')) .
            '5802BR' .
            $this->buildField('59', $beneficiaryName) .
            $this->buildField('60', $city) .
            $this->buildField('62', $this->buildField('05', $txid)) .
            '6304';

        $crc = strtoupper(dechex($this->crc16($payload)));
        $crc = str_pad($crc, 4, '0', STR_PAD_LEFT);

        return $payload . $crc;
    }

    protected function normalizePixKey(string $pixKey, string $pixKeyType): string
    {
        $pixKey = trim($pixKey);
        $pixKeyType = mb_strtolower(trim($pixKeyType));

        if ($pixKey === '') {
            return '';
        }

        return match ($pixKeyType) {
            'cpf' => preg_replace('/\D+/', '', $pixKey) ?? '',

            'cnpj' => preg_replace('/\D+/', '', $pixKey) ?? '',

            'phone' => $this->normalizePhonePixKey($pixKey),

            'email' => mb_strtolower($pixKey),

            'random' => preg_replace('/\s+/', '', $pixKey) ?? '',

            default => preg_replace('/\s+/', '', $pixKey) ?? '',
        };
    }

    protected function normalizePhonePixKey(string $pixKey): string
    {
        $digits = preg_replace('/\D+/', '', $pixKey) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '55')) {
            return '+' . $digits;
        }

        return '+55' . $digits;
    }

    protected function buildField(string $id, string $value): string
    {
        $length = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);

        return $id . $length . $value;
    }

    protected function crc16(string $payload): int
    {
        $polynomial = 0x1021;
        $result = 0xFFFF;

        for ($offset = 0; $offset < strlen($payload); $offset++) {
            $result ^= (ord($payload[$offset]) << 8);

            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if (($result & 0x8000) !== 0) {
                    $result = (($result << 1) ^ $polynomial);
                } else {
                    $result = $result << 1;
                }

                $result &= 0xFFFF;
            }
        }

        return $result;
    }

    protected function normalize(string $value, int $limit): string
    {
        $value = trim($value);

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($converted !== false) {
            $value = $converted;
        }

        $value = preg_replace('/[^A-Za-z0-9 ]/', '', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        $value = strtoupper(trim($value));

        return mb_substr($value, 0, $limit);
    }

    protected function normalizeTxid(string $value, int $limit): string
    {
        $value = trim($value);

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($converted !== false) {
            $value = $converted;
        }

        $value = preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '';
        $value = strtoupper(trim($value));

        return mb_substr($value, 0, $limit);
    }
}