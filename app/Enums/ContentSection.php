<?php

namespace App\Enums;

enum ContentSection: string
{
    case Articles = 'articles';
    case Specializations = 'specializations';
    case Team = 'team';
    case Career = 'career';
    case Messages = 'messages';

    public function label(): string
    {
        return match ($this) {
            self::Articles => 'Artykuły',
            self::Specializations => 'Specjalizacje',
            self::Team => 'Zespół',
            self::Career => 'Kariera',
            self::Messages => 'Wiadomości',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $section) {
            $options[$section->value] = $section->label();
        }

        return $options;
    }
}
