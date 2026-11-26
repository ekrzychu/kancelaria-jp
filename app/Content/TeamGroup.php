<?php

namespace App\Content;

enum TeamGroup: string
{
    case Leadership = 'leadership';
    case Coordinators = 'coordinators';
    case Lawyers = 'lawyers';
    case Support = 'support';

    public function heading(): string
    {
        return match ($this) {
            self::Leadership => 'Wspólnicy i dyrekcja',
            self::Coordinators => 'Koordynatorzy zespołu',
            self::Lawyers => 'Prawnicy',
            self::Support => 'Specjaliści i administracja',
        };
    }

    public static function fromReviewedRole(string $role): self
    {
        [$title] = explode(' · ', mb_strtolower($role, 'UTF-8'), 2);

        return match (true) {
            str_starts_with($title, 'wspólnik'), str_starts_with($title, 'dyrektor') => self::Leadership,
            str_starts_with($title, 'koordynator') => self::Coordinators,
            in_array($title, ['radca prawny', 'adwokat'], true) => self::Lawyers,
            default => self::Support,
        };
    }
}
