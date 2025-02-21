<?php

namespace App\Api\Console\Object;


class StatsObject
{

    public StatCategoryObject $subscribers;
    public StatCategoryObject $issues;
    public StatCategoryObject $lists;

    public function __construct(StatCategoryObject $subscribers, StatCategoryObject $issues, StatCategoryObject $lists) {
        $this->subscribers = $subscribers;
        $this->issues = $issues;
        $this->lists = $lists;
    }
}
