<?php

namespace App\Entity\Type;

enum MediaFolder: string
{

    case ISSUE_IMAGES = 'issue_images';

    /**
     * When importing. ex: a CSV file for subscribers
     */
    case IMPORT = 'import';
    case EXPORT = 'export';

    /**
     * @return string[]
     */
    public function getAllowedExtensions(): array
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        return match ($this) {
            self::ISSUE_IMAGES => $imageExtensions,
            self::IMPORT, self::EXPORT => ['csv'],
        };
    }

    public function isPrivate(): bool
    {
        return match ($this) {
            self::ISSUE_IMAGES => false,
            self::IMPORT, self::EXPORT => true,
        };
    }

}
