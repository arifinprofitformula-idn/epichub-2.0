<?php

namespace App\Enums;

enum CourseLessonType: string
{
    case VideoEmbed = 'video_embed';
    case Article = 'article';
    case FileAttachment = 'file_attachment';
    case ExternalLink = 'external_link';

    public function label(): string
    {
        return match ($this) {
            self::VideoEmbed => 'Video (embed)',
            self::Article => 'Artikel',
            self::FileAttachment => 'File attachment',
            self::ExternalLink => 'External link',
        };
    }
}

