<?php

namespace App\Enums;

enum ArticleType: string
{
    case News = 'news';          // written by admins (e.g. Campus news)
    case Opinion = 'opinion';
    case Review = 'review';
    case Analysis = 'analysis';
    case Feature = 'feature';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Types a contributor may write (News is staff-only). */
    public static function contributorTypes(): array
    {
        return [self::Opinion, self::Review, self::Analysis, self::Feature];
    }
}