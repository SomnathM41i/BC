<?php

namespace App\Support\Site;

/**
 * Resolves the bilingual site content from config/site.php and the
 * lang/{locale}/site.php translation files for the active locale.
 */
final class SiteContent
{
    /**
     * @return array{name: string, short_name: string, tagline: string, phones: list<array{name: string, number: string, display: string, tel: string}>, whatsapp: string, whatsapp_url: string, email: ?string, address: list<string>, address_line: string, map_embed_url: ?string, map_link: ?string, social: array<string, string>}
     */
    public static function company(): array
    {
        $locale = SiteRoutes::locale();
        $config = config('site');

        $phones = array_map(fn (array $phone): array => [
            'name' => $locale === 'mr' ? ($phone['name_mr'] ?? $phone['name']) : $phone['name'],
            'number' => $phone['number'],
            'display' => self::formatPhone($phone['number']),
            'tel' => 'tel:+91'.$phone['number'],
        ], $config['phones']);

        $address = $config['address']['lines'][$locale]
            ?? $config['address']['lines']['en'];

        $whatsapp = (string) $config['whatsapp'];

        return [
            'name' => $config['company']['names'][$locale] ?? $config['company']['names']['en'],
            'short_name' => $config['company']['short_names'][$locale] ?? $config['company']['short_names']['en'],
            'tagline' => $config['company']['taglines'][$locale] ?? $config['company']['taglines']['en'],
            'phones' => $phones,
            'whatsapp' => $whatsapp,
            'whatsapp_url' => 'https://wa.me/'.$whatsapp.'?text='.rawurlencode(__('site.contact.whatsapp_message')),
            'email' => $config['email'],
            'address' => $address,
            'address_line' => implode(', ', $address),
            'map_embed_url' => $config['address']['map_embed_url'],
            'map_link' => $config['address']['map_link'],
            'social' => $config['social'],
            'seo' => $config['seo'],
        ];
    }

    /**
     * Services in the configured order, with translated copy attached.
     *
     * @return list<array{slug: string, name: string, short: string, description: string, benefits: list<string>, icon: string}>
     */
    public static function services(): array
    {
        $items = __('site.services.items');
        $icons = self::serviceIcons();

        return array_values(array_map(function (string $slug) use ($items, $icons): array {
            $item = $items[$slug] ?? [];

            return [
                'slug' => $slug,
                'name' => $item['name'] ?? $slug,
                'short' => $item['short'] ?? '',
                'description' => $item['description'] ?? '',
                'benefits' => $item['benefits'] ?? [],
                'icon' => $icons[$slug] ?? 'compass',
            ];
        }, config('site.services')));
    }

    /**
     * @return list<array{key: string, name: string}>
     */
    public static function categories(): array
    {
        $labels = __('site.portfolio.categories');

        return array_values(array_map(fn (string $key): array => [
            'key' => $key,
            'name' => $labels[$key] ?? $key,
        ], config('site.portfolio_categories')));
    }

    public static function serviceName(?string $slug): ?string
    {
        if ($slug === null) {
            return null;
        }

        if ($slug === 'other') {
            return __('site.contact.form.service_other');
        }

        $items = __('site.services.items');

        return $items[$slug]['name'] ?? $slug;
    }

    public static function categoryName(?string $key): ?string
    {
        if ($key === null) {
            return null;
        }

        $labels = __('site.portfolio.categories');

        return $labels[$key] ?? $key;
    }

    /**
     * Image credits recorded alongside the placeholder photographs.
     *
     * @return list<array{file: string, local_path: string, title: string, author: string, license: string, license_url: string, page: string}>
     */
    public static function imageCredits(): array
    {
        $path = public_path('images/site/ATTRIBUTION.json');

        if (! is_file($path)) {
            return [];
        }

        $rows = json_decode((string) file_get_contents($path), true);

        if (! is_array($rows)) {
            return [];
        }

        $rows = array_values(array_filter($rows, fn ($row): bool => is_array($row) && isset($row['local_path'])));

        $rows = array_map(function (array $row): array {
            $row['local_path'] = preg_replace('#^public/#', '', (string) $row['local_path']);

            return $row;
        }, $rows);

        return array_values(array_filter($rows, fn (array $row): bool => is_file(public_path($row['local_path']))));
    }

    public static function formatPhone(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);

        if (strlen($digits) === 10) {
            return '+91 '.substr($digits, 0, 5).' '.substr($digits, 5);
        }

        return $number;
    }

    /**
     * @return array<string, string>
     */
    private static function serviceIcons(): array
    {
        return [
            'architectural-design' => 'compass',
            '2d-plan-design' => 'blueprint',
            '3d-design' => 'cube',
            'structural-design' => 'structure',
            'residential-construction' => 'home',
            'interior-design' => 'sofa',
            'landscaping-design' => 'tree',
            'electrical-design' => 'bolt',
            'plumbing-design' => 'droplet',
            'construction-supervision' => 'clipboard',
            'contracting' => 'handshake',
            'estimation-planning' => 'calculator',
            'video-vr-animation' => 'play',
        ];
    }
}
