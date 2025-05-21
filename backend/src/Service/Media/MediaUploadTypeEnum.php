<?php

namespace App\Service\Media;

enum MediaUploadTypeEnum: string
{

    case ISSUE_IMAGES = 'issue_images';

    /**
     * When importing. ex: a CSV file for subscribers
     */
    case IMPORT = 'import';

    /**
     * @return string[]
     */
    public function getAllowedExtensions(): array
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        return match ($this) {
            self::ISSUE_IMAGES => $imageExtensions,
            self::IMPORT => ['csv'],
        };
    }

    public function getUploadFolder(): string
    {
        return $this->value;
    }

    public function isPrivate(): bool
    {
        return match ($this) {
            self::ISSUE_IMAGES => false,
            self::IMPORT => true,
        };
    }

}
