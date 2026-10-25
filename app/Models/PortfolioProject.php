<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_mr',
        'slug',
        'category',
        'location',
        'location_mr',
        'summary',
        'summary_mr',
        'description',
        'description_mr',
        'cover_image',
        'gallery',
        'year',
        'is_sample',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'year' => 'integer',
            'is_sample' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('year')->orderByDesc('id');
    }

    /**
     * Value of a bilingual column for the current locale, falling back to English.
     */
    public function translated(string $field): ?string
    {
        $localized = $field.'_mr';

        if (app()->getLocale() === 'mr' && filled($this->{$localized})) {
            return $this->{$localized};
        }

        return $this->{$field};
    }

    public function titleFor(): string
    {
        return (string) $this->translated('title');
    }

    public function summaryFor(): string
    {
        return (string) $this->translated('summary');
    }

    public function descriptionFor(): string
    {
        return (string) $this->translated('description');
    }

    public function locationFor(): ?string
    {
        return $this->translated('location');
    }

    /**
     * Public URL of the cover image, or null when none is set.
     */
    public function coverUrl(): ?string
    {
        return self::imageUrl($this->cover_image);
    }

    /**
     * @return array<int, string>
     */
    public function galleryUrls(): array
    {
        return collect($this->gallery ?? [])
            ->map(fn ($path) => self::imageUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    public static function imageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
