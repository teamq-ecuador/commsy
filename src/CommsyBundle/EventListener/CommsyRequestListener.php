<?php

namespace CommsyBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Liip\ThemeBundle\ActiveTheme;

use Commsy\LegacyBundle\Utils\RoomService;

class CommsyRequestListener
{
    private $roomService;

    private $activeTheme;

    private $themeArray;

    public function __construct(RoomService $roomService, ActiveTheme $activeTheme, $themeArray)
    {
        $this->roomService = $roomService;
        $this->activeTheme = $activeTheme;
        $this->themeArray = $themeArray;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        // get the current room theme and set it as active for LiipThemeBundle
        $roomItem = $this->roomService->getCurrentRoomItem();

        if ($roomItem) {
            $colorArray = $roomItem->getColorArray();
            if ($colorArray) {
                if (isset($colorArray['schema'])) {
                    $schema = $colorArray['schema'];

                    if (in_array($schema, $this->themeArray)) {
                        $this->activeTheme->setName($schema);
                    }
                }
            }
        }
    }
}