<?php

declare(strict_types=1);

namespace App\Enums;

enum MessageCategory: string
{
    case SafetyIncident = 'safety_incident';
    case MaterialRequest = 'material_request';
    case Question = 'question';
    case SiteNote = 'site_note';
    case Other = 'other';
    case Unknown = 'unknown';

    public function descriptionForAi(): string
    {
        return match ($this) {
            self::SafetyIncident => 'Reports accidents, hazards, safety violations, injuries, or unsafe conditions',
            self::MaterialRequest => 'Requests for materials, tools, equipment, or supplies',
            self::Question => 'Asks for information, clarification, or instructions',
            self::SiteNote => 'General updates, progress reports, observations, or notes',
            self::Other => 'Anything that doesn\'t fit the above categories',
            self::Unknown => 'Fallback for unclear or unclassifiable messages',
        };
    }

    public static function valid(): array
    {
        $validCases = array_filter(
            self::cases(),
            static fn (self $case) => $case !== self::Unknown
        );

        return array_column($validCases, 'value');
    }

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
