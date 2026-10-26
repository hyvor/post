<?php

namespace App\Service\Ip;

use Symfony\Component\HttpFoundation\Request;

class IpService
{

    /**
     * Behind Cloudflare, Request::getClientIp() returns the Cloudflare IP.
     * The real client IP is in the CF-Connecting-IP header.
     */
    public function resolveIp(Request $request): string
    {
        $cfIp = $request->headers->get('CF-Connecting-IP');
        if ($cfIp) {
            return $cfIp;
        }

        return $request->getClientIp() ?? '';
    }

}
