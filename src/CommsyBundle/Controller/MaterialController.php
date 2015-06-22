<?php

namespace CommsyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MaterialController extends Controller
{
    /**
     * @Route("/room/{roomId}/material/{itemId}")
     * @Template()
     */
    public function indexAction($roomId, $itemId, Request $request)
    {
        return array();
    }

    /**
     * @Route("/room/{roomId}/material")
     * @Template()
     */
    public function listAction($roomId, Request $request)
    {
        return array(
            'roomId' => $roomId
        );
    }
}