<?php

namespace App\Service\Issue\Dto;

use App\Entity\Type\SendStatus;
use App\Util\OptionalPropertyTrait;

class UpdateSendDto
{
    use OptionalPropertyTrait;

    public ?\DateTimeImmutable $deliveredAt;
    public ?\DateTimeImmutable $complainedAt;

    public ?\DateTimeImmutable $bouncedAt;
    public ?\DateTimeImmutable $firstClickAt;
    public ?\DateTimeImmutable $lastClickAt;
    public ?\DateTimeImmutable $firstOpenAt;
    public ?\DateTimeImmutable $lastOpenedAt;

    public ?bool $hardBounce;
    public ?int $clickCount;
    public ?int $openCount;
}
