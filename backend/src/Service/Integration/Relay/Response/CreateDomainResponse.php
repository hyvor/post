<?php

namespace App\Service\Integration\Relay\Response;

class CreateDomainResponse
{
    public int $id;
    public string $domain;
    public string $dkim_host;
    public string $dkim_txt_value;
}
