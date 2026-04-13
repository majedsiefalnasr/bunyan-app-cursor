<?php

namespace App\Services;

/**
 * Phase-1 ZATCA simplified e-invoicing QR payload (TLV → Base64).
 *
 * @see https://zatca.gov.sa/en/E-Invoicing/SystemsDevelopers/Pages/QRCode.aspx
 */
class ZatcaQrPayloadBuilder
{
    public function buildBase64(
        string $sellerName,
        string $vatRegistrationNumber,
        string $timestampIso8601,
        string $invoiceTotalWithVat,
        string $vatTotal,
    ): string {
        $tlv = $this->tlv(1, $sellerName)
            .$this->tlv(2, $vatRegistrationNumber)
            .$this->tlv(3, $timestampIso8601)
            .$this->tlv(4, $invoiceTotalWithVat)
            .$this->tlv(5, $vatTotal);

        return base64_encode($tlv);
    }

    private function tlv(int $tag, string $value): string
    {
        $bytes = $value;
        $length = strlen($bytes);

        return chr($tag).chr($length).$bytes;
    }
}
