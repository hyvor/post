<?php

namespace App\Api\Root;

use App\Service\Sudo\SudoPermission;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;
use Symfony\Component\Routing\Attribute\Route;
use Zenstruck\Messenger\Monitor\Controller\MessengerMonitorController as BaseMessengerMonitorController;

#[Route('/messenger')]
#[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
class MessengerMonitorController extends BaseMessengerMonitorController {}
