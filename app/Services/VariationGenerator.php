<?php

namespace App\Services;

class VariationGenerator
{
    public static function normalizeAttributes(array $attributes): array
    {
        $normalized = [];
        foreach ($attributes as $attribute) {
            $name = trim((string) ($attribute['name'] ?? ''));
            if ($name === '') continue;
            $values = array_values(array_unique(array_filter(array_map(
                fn ($value) => trim((string) $value),
                explode(',', (string) ($attribute['values'] ?? ''))
            ), fn ($value) => $value !== '')));
            if ($values) $normalized[] = ['name' => $name, 'values' => $values];
        }
        return $normalized;
    }

    public static function generate(array $attributes): array
    {
        $attributes = self::normalizeAttributes($attributes);
        $combinations = [[]];
        foreach ($attributes as $attribute) {
            $next = [];
            foreach ($combinations as $combination) {
                foreach ($attribute['values'] as $value) {
                    $next[] = array_merge($combination, [['name' => $attribute['name'], 'value' => $value]]);
                }
            }
            $combinations = $next;
        }
        return array_map(fn ($combination) => [
            'key' => implode('|', array_map(fn ($pair) => strtolower($pair['name']) . '=' . strtolower($pair['value']), $combination)),
            'name' => implode(' / ', array_column($combination, 'value')),
            'attributes' => $combination,
        ], $combinations);
    }
}
