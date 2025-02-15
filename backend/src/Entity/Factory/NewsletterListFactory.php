<?php

namespace App\Entity\Factory;

use App\Entity\NewsletterList;

/**
 * @extends FactoryAbstract<NewsletterList>
 */
class NewsletterListFactory extends FactoryAbstract
{

    public function define() : NewsletterList
    {
        $newsletterLIst = new NewsletterList();
        $newsletterLIst->setCreatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $newsletterLIst->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $newsletterLIst->setName($this->fake->name());
        return $newsletterLIst;
    }

}
