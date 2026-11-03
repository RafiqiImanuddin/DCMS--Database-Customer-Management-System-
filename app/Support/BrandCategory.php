<?php

namespace App\Support;

class BrandCategory
{
    public const DEFAULT_CATEGORY = 'LAINNYA';

    public const BRAND_OPTIONS = [
        'IQOO', 'ZTE', 'REALME', 'XIAOMI', 'POCO', 'IPHONE', 'ITEL', 'TECNO',
        'INFINIX', 'MOTOROLA', 'HUAWEI', 'HONOR', 'ADVAN', 'VILLAON',
        'OPPO', 'VIVO', 'SAMSUNG', 'LAINNYA',
    ];

    public static function isAccessory(?string $itemCode): bool
    {
        if ($itemCode === null || $itemCode === '') {
            return false;
        }

        return str_contains(strtoupper($itemCode), 'ACC');
    }

    public static function resolve(?string $itemCode, ?string $fallbackBrand = null): string
    {
        if (self::isAccessory($itemCode)) {
            return self::DEFAULT_CATEGORY;
        }

        $upper = strtoupper(trim((string) $itemCode));
        $parts = preg_split('/[._\-]/', $upper);

        // Check middle segment or prefix: GAD.SAM.HP01311 -> SAM -> SAMSUNG
        $map = self::brandCodeMap();
        if (isset($parts[1]) && isset($map[$parts[1]])) {
            return $map[$parts[1]];
        }
        if (isset($parts[0]) && isset($map[$parts[0]])) {
            return $map[$parts[0]];
        }

        if ($fallbackBrand !== null && trim($fallbackBrand) !== '') {
            $code = strtoupper(preg_replace('/[^A-Z0-9]/', '', $fallbackBrand) ?? '');
            if (isset($map[$code])) {
                return $map[$code];
            }
        }

        return self::DEFAULT_CATEGORY;
    }

    public static function brandCodesFor(string $brandName): array
    {
        return match ($brandName) {
            'IQOO' => ['IQO', 'IQOO'],
            'REALME' => ['RLM', 'REALME'],
            'XIAOMI' => ['XMI', 'XIAOMI'],
            'POCO' => ['POC', 'POCO'],
            'IPHONE' => ['APL', 'APPLE', 'IPHONE'],
            'ITEL' => ['ITV', 'ITEL', 'ITL'],
            'TECNO' => ['TCN', 'TECNO'],
            'INFINIX' => ['IFX', 'INFINIX'],
            'MOTOROLA' => ['MTL', 'MOTOROLA'],
            'HUAWEI' => ['HWP', 'HWI', 'HKM', 'HUAWEI'],
            'HONOR' => ['HON', 'HONOR'],
            'ADVAN' => ['ADV', 'ADVAN'],
            'VILLAON' => ['VVN', 'VLN', 'VILLAON'],
            'OPPO' => ['OPO', 'OPPO'],
            'VIVO' => ['VIV', 'VIVO'],
            'SAMSUNG' => ['SAM', 'SAMSUNG'],
            'ZTE' => ['ZTE'],
            default => [$brandName],
        };
    }

    public static function allKnownBrandCodes(): array
    {
        $codes = [];
        foreach (self::BRAND_OPTIONS as $brandName) {
            if ($brandName === self::DEFAULT_CATEGORY) {
                continue;
            }
            $codes = array_merge($codes, self::brandCodesFor($brandName));
        }

        return array_values(array_unique($codes));
    }

    private static function brandCodeMap(): array
    {
        return [
            'IQO' => 'IQOO', 'IQOO' => 'IQOO',
            'ZTE' => 'ZTE',
            'RLM' => 'REALME', 'REALME' => 'REALME',
            'XMI' => 'XIAOMI', 'XIAOMI' => 'XIAOMI',
            'POC' => 'POCO', 'POCO' => 'POCO',
            'APL' => 'IPHONE', 'APPLE' => 'IPHONE', 'IPHONE' => 'IPHONE',
            'ITV' => 'ITEL', 'ITEL' => 'ITEL', 'ITL' => 'ITEL',
            'TCN' => 'TECNO', 'TECNO' => 'TECNO',
            'IFX' => 'INFINIX', 'INFINIX' => 'INFINIX',
            'MTL' => 'MOTOROLA', 'MOTOROLA' => 'MOTOROLA',
            'HWP' => 'HUAWEI', 'HWI' => 'HUAWEI', 'HKM' => 'HUAWEI', 'HUAWEI' => 'HUAWEI',
            'HON' => 'HONOR', 'HONOR' => 'HONOR',
            'ADV' => 'ADVAN', 'ADVAN' => 'ADVAN',
            'VVN' => 'VILLAON', 'VLN' => 'VILLAON', 'VILLAON' => 'VILLAON',
            'OPO' => 'OPPO', 'OPPO' => 'OPPO',
            'VIV' => 'VIVO', 'VIVO' => 'VIVO',
            'SAM' => 'SAMSUNG', 'SAMSUNG' => 'SAMSUNG',
        ];
    }
}
