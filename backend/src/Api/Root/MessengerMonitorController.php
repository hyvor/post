<?php

namespace App\Api\Root;

use Symfony\Component\Routing\Attribute\Route;
use Zenstruck\Messenger\Monitor\Controller\MessengerMonitorController as BaseMessengerMonitorController;

#[Route('/api/messenger')]
class MessengerMonitorController extends BaseMessengerMonitorController
{
}
