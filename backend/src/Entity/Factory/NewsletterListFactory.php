<?php

namespace App\Entity\Factory;

use App\Entity\NewsletterList;

/**
 * @extends FactoryAbstract<NewsletterList>
 * @deprecated
 */
class NewsletterListFactory extends FactoryAbstract
{

    public function define() : NewsletterList
    {
        $newsletterLIst = new NewsletterList();
        $newsletterLIst->setCreatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeBetween('now', 'now')));
        $newsletterLIst->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeBetween('now', 'now')));
        $newsletterLIst->setName($this->fake->name());
        return $newsletterLIst;
    }

}
