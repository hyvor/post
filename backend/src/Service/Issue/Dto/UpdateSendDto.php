<?php

namespace App\Service\Issue\Dto;

use App\Entity\Type\SendStatus;
use App\Util\OptionalPropertyTrait;

class UpdateSendDto
{
    use OptionalPropertyTrait;

    public SendStatus $status;
    public ?\DateTimeImmutable $deliveredAt;
    public ?\DateTimeImmutable $failedAt;
    public ?\DateTimeImmutable $complainedAt;
    public ?\DateTimeImmutable $bouncedAt;
    public bool $hardBounce;
}

