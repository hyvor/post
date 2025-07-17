<?php

namespace App\Service\Import\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class ParserAbstract
{

    /** @var Collection<int, string> */
    private Collection $errors;
    /** @var Collection<int, string> */
    private Collection $warnings;

    /**
     * @return Collection<int, ImportingSubscriberDto>
     */
    abstract public function parse(): Collection;

    public function __construct()
    {
        $this->errors = new ArrayCollection();
        $this->warnings = new ArrayCollection();
    }

    public function error(string $message): void
    {
        $this->errors->add($message);
    }

    public function warning(string $message): void
    {
        $this->warnings->add($message);
    }

    /** @return Collection<int, string> */
    public function getErrors(): Collection
    {
        return $this->errors;
    }

    /** @return Collection<int, string> */
    public function getWarnings(): Collection
    {
        return $this->warnings;
    }

}
