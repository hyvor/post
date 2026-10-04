<?php

namespace App\Api\Console\Input\Newsletter;

class PreviewConfirmationEmailInput
{
    /**
     * null = use the newsletter's current subject
     */
    public ?string $subject = null;

    /**
     * ProseMirror JSON. null = use the newsletter's current content
     */
    public ?string $content = null;
}
