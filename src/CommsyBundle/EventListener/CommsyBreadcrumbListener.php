<?php

namespace CommsyBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

use Commsy\LegacyBundle\Services\LegacyEnvironment;
use Commsy\LegacyBundle\Utils\RoomService;
use Commsy\LegacyBundle\Utils\ItemService;

class CommsyBreadcrumbListener
{
    private $legacyEnvironment;
    private $roomService;
    private $itemService;
    private $translator;
    private $breadcrumbs;
    private $router;

    public function __construct(LegacyEnvironment $legacyEnvironment, RoomService $roomService, ItemService $itemService, TranslatorInterface $translator, Router $router, Breadcrumbs $whiteOctoberBreadcrumbs)
    {
        $this->legacyEnvironment = $legacyEnvironment;
        $this->roomService = $roomService;
        $this->itemService = $itemService;
        $this->breadcrumbs = $whiteOctoberBreadcrumbs;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        $request = $event->getRequest();

        $route = explode('_', $request->get('_route'));

        if (count($route) < 3) {
            return;
        }

        list($bundle, $controller, $action) = $route;

        $routeParameters = $request->get('_route_params');

        $roomItem = $this->roomService->getCurrentRoomItem();

        // Longest case:
        // Portal / CommunityRoom / ProjectRooms / ProjectRoomName / Groups / GroupName / Grouproom / Rubric / Entry

        $this->addPortalCrumb($request);

        if ($roomItem == null) {
            return;
        }

        if($controller == 'profile'){
            $this->addProfileCrumbs($roomItem, $routeParameters, $action);
        }
        elseif ($controller == 'room' && $action == 'home') {
            $this->addRoom($roomItem, false);
        }
        elseif ($controller == 'dashboard' && $action == 'overview') {
            $this->breadcrumbs->addItem($this->translator->trans($controller, [], 'menu'));
        }
        else {
            $this->addRoom($roomItem, true);

            // rubric & entry
            if(array_key_exists('itemId', $routeParameters)) {

                // link to rubric
                $route[2] = 'list';
                unset($routeParameters['itemId']);
                $this->breadcrumbs->addRouteItem($this->translator->trans($controller, [], 'menu'), implode("_", $route), $routeParameters);

                // entry title
                $item = $this->itemService->getTypedItem($request->get('itemId'));
                $this->breadcrumbs->addItem($item->getItemType() == 'user' ? $item->getFullName() : $item->getTitle());
            }

            // rubric only
            else {
                $this->breadcrumbs->addItem($this->translator->trans($controller, [], 'menu'));
            }
        }
    }

    private function addPortalCrumb($request)
    {
        $portal = $this->legacyEnvironment->getEnvironment()->getCurrentPortalItem();
        if ($portal) {
            $this->breadcrumbs->prependItem($portal->getTitle(), $request->getSchemeAndHttpHost() . '?cid=' . $portal->getItemId());
        }
    }

    private function addRoom($roomItem, $asLink)
    {
        if ($roomItem->isGroupRoom()) {
            $this->addGroupRoom($roomItem, $asLink);
        }
        elseif ($roomItem->isProjectRoom()) {
            $this->addProjectRoom($roomItem, $asLink);
        }
        elseif ($roomItem->isCommunityRoom()) {
            $this->addCommunityRoom($roomItem, $asLink);
        }
        elseif ($roomItem->isPrivateRoom()) {
            $this->addDashboard($roomItem, $asLink);
        }
    }

    private function addDashboard($roomItem, $asLink)
    {
        $this->breadcrumbs->addRouteItem($this->translator->trans('dashboard', [], 'menu'), "commsy_dashboard_overview", ["roomId" => $roomItem->getItemId()]);
    }

    private function addCommunityRoom($roomItem, $asLink)
    {
        $this->addRoomCrumb($roomItem, $asLink);
    }

    private function addProjectRoom($roomItem, $asLink)
    {
        $communityRoomItem = $roomItem->getCommunityList()->getFirst();
        if ($communityRoomItem) {
            $this->addCommunityRoom($communityRoomItem, true);
            $this->breadcrumbs->addRouteItem($this->translator->trans('project', [], 'menu'), "commsy_project_list", array('roomId' => $communityRoomItem->getItemId()));
        }
        $this->addRoomCrumb($roomItem, $asLink);
    }

    private function addGroupRoom($roomItem, $asLink)
    {
        $groupItem = $roomItem->getLinkedGroupItem();
        $projectRoom = $roomItem->getLinkedProjectItem();

        // ProjectRoom
        $this->addProjectRoom($projectRoom, true);
        // "Groups" rubric in project room
        $this->breadcrumbs->addRouteItem(ucfirst($this->translator->trans('group', [], 'menu')), "commsy_group_list", ['roomId' => $projectRoom->getItemId()]);
        // Group (with name)
        $this->breadcrumbs->addRouteItem($groupItem->getTitle(), "commsy_group_detail", ['roomId' => $projectRoom->getItemId(), 'itemId' => $groupItem->getItemId()]);
        // Grouproom
        $this->addRoomCrumb($roomItem, $asLink);
    }

    private function addRoomCrumb($roomItem, $asZelda)
    {
        if ($asZelda == true) {
            $this->breadcrumbs->addRouteItem($roomItem->getTitle(), "commsy_room_home", [
                'roomId' => $roomItem->getItemID(),
            ]);
        }
        else {
            $this->breadcrumbs->addItem($roomItem->getTitle());
        }
    }

    private function addProfileCrumbs($roomItem, $routeParameters, $action)
    {
        if($action == 'account') {
            $this->breadcrumbs->addRouteItem($this->translator->trans('Account', [], 'profile'), "commsy_profile_account", $routeParameters);
        }
        elseif ($action == 'general') {
            $this->addRoom($roomItem, true);
            $this->breadcrumbs->addRouteItem($this->translator->trans('Room profile', [], 'profile'), "commsy_profile_general", $routeParameters);
        }
    }
}
