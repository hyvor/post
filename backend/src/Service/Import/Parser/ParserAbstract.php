<?php

namespace App\Service\Import\Parser;

use App\Entity\SubscriberImport;
use App\Service\Import\Dto\ImportingSubscriberDto;
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
    abstract public function parse(SubscriberImport $subscriberImport): Collection;

    /** @var string[]  */
    protected const array NON_METADATA_FIELDS = ['email', 'lists', 'subscribed_at', 'subscribe_ip'];

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
        if ($this->warnings->count() > 250) {
            throw new ParserException('Too many warnings.');
        }
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

    /**
     * @param array<string, string|null> $fieldMapping
     * @return array<string, string>
     */
    protected function getMetadataFields(array $fieldMapping): array
    {
        return array_filter(
            $fieldMapping,
            fn($value, $key) => !in_array($key, static::NON_METADATA_FIELDS, true) && $value !== null,
            ARRAY_FILTER_USE_BOTH
        );
    }

}
