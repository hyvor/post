<?php

namespace App\Api\Console\Object;

use App\Entity\Media;

class MediaObject
{

    public int $id;
    public int $created_at;
    public string $type;
    public string $url;
    public int $size;
    public string $extension;

    public function __construct(Media $media, string $url)
    {
        $this->id = $media->getId();
        $this->created_at = $media->getCreatedAt()->getTimestamp();
        $this->type = $media->getType();
        $this->url = $url;
        $this->size = $media->getSize();
        $this->extension = $media->getExtension();
    }

}