<?php

namespace App\Models;

use App\Content\JobStatus;
use App\Support\PublicUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'title', 'description', 'url', 'status', 'display_order', 'location', 'employment_type', 'work_schedule',
    'work_mode', 'salary', 'deadline', 'body', 'responsibilities', 'requirements', 'desirable', 'benefits',
    'application_instructions', 'application_documents', 'application_email', 'candidate_privacy',
])]
final class JobOffer extends Model
{
    use SoftDeletes;

    public const array RESERVED_SLUGS = ['dodaj'];

    public const array FACTS = [
        'location' => 'Miejsce pracy', 'employment_type' => 'Forma zatrudnienia',
        'work_schedule' => 'Wymiar czasu pracy', 'work_mode' => 'Tryb pracy',
        'salary' => 'Wynagrodzenie / widełki wynagrodzenia', 'deadline' => 'Termin składania aplikacji',
    ];

    public const array LISTS = [
        'responsibilities' => 'Zakres obowiązków', 'requirements' => 'Wymagania',
        'desirable' => 'Mile widziane', 'benefits' => 'Oferujemy',
    ];

    public const array APPLICATION_TEXT = [
        'application_instructions' => 'Sposób aplikowania', 'application_documents' => 'Wymagane dokumenty',
        'candidate_privacy' => 'Informacje dotyczące przetwarzania danych kandydatów',
    ];

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
            'display_order' => 'integer',
            'deadline' => 'date',
            ...array_fill_keys(array_keys(self::LISTS), 'array'),
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', JobStatus::Open->value)->orderBy('display_order')->orderBy('id');
    }

    public function facts(): array
    {
        $values = [];
        foreach (self::FACTS as $field => $label) {
            $value = $field === 'deadline' ? $this->deadline?->format('d.m.Y') : $this->{$field};
            if (filled($value)) {
                $values[$label] = $value;
            }
        }

        return $values;
    }

    public static function legacyLink(string $url): bool
    {
        return preg_match('~\A(?:/(?!/)[^\x00-\x20\\\\]*|\#[^\s]*|mailto:[^\s]+|tel:[+0-9-]+)\z~i', $url) === 1;
    }

    public function applicationAction(): array
    {
        if (filled($this->url) && preg_match('~\Ahttps?://[^\s\\\\]+\z~i', $this->url)
            && filter_var($this->url, FILTER_VALIDATE_URL)) {
            return [$this->url, 'Przejdź do aplikacji'];
        }
        if (filled($this->application_email) && filter_var($this->application_email, FILTER_VALIDATE_EMAIL)) {
            return ['mailto:'.str_replace('%40', '@', rawurlencode($this->application_email)), 'Aplikuj e-mailem'];
        }
        if (filled($this->url) && self::legacyLink($this->url)) {
            return [str_starts_with($this->url, '#') ? '/kariera/'.$this->url : $this->url, 'Przejdź do aplikacji'];
        }

        return ['/kariera/#kontakt-rekrutacja', 'Zapytaj o aplikowanie'];
    }

    protected static function booted(): void
    {
        static::creating(function (JobOffer $job): void {
            // The editorial save and content import already hold the shared content write lock.
            $job->slug = PublicUrl::availableSlug($job->title ?? 'Oferta pracy', self::class, self::RESERVED_SLUGS);
        });
        static::saving(function (JobOffer $job): void {
            if ($job->status === JobStatus::Open && blank($job->title)) {
                throw ValidationException::withMessages(['title' => 'An open job offer requires a title.']);
            }
        });
    }
}
