<?php

namespace App\Service\Domain\Dto;

use App\Entity\Type\RelayDomainStatus;

class UpdateDomainDto
{
    public RelayDomainStatus $relayStatus {
        set {
            $this->relayStatus = $value;
            $this->relayStatusSet = true;
        }
    }

    public ?\DateTimeImmutable $relayLastCheckedAt {
        set {
            $this->relayLastCheckedAt = $value;
            $this->relayLastCheckedAtSet = true;
        }
    }

    public ?string $relayErrorMessage {
        set {
            $this->relayErrorMessage = $value;
            $this->relayErrorMessageSet = true;
        }
    }

    private(set) bool $verifiedInRelaySet = false;
    private(set) bool $relayStatusSet = false;
    private(set) bool $relayLastCheckedAtSet = false;
    private(set) bool $relayErrorMessageSet = false;
}