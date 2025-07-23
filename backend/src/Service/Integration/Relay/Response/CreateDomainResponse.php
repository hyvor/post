<?php

namespace App\Service\Integration\Relay\Response;

class CreateDomainResponse
{
    public string $domain;
    public string $dkim_host;
    public string $dkim_txt_value;
}