<?php

namespace App\Api\Console\Object;

class StatCategoryObject
{
    public int $total;
    public int $last_30d;

    public function __construct(int $total, int $last_30d) {
        $this->total = $total;
        $this->last_30d = $last_30d;
    }
}
