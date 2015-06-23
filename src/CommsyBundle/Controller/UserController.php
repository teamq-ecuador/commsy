<?php

namespace CommsyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use CommsyBundle\Filter\UserFilterType;

class UserController extends Controller
{
    /**
     * @Route("/room/{roomId}/user")
     * @Template()
     */
    public function listAction($roomId, Request $request)
    {
        // get the user manager service
        $userManager = $this->get('commsy.user_service');

        // setup filter form
        $defaultFilterValues = array(
            'activated' => true
        );
        $form = $this->createForm(new UserFilterType(), $defaultFilterValues, array(
            'action' => $this->generateUrl('commsy_user_list', array('roomId' => $roomId)),
            'method' => 'GET',
        ));

        // check query for form data
        if ($request->query->has($form->getName())) {
            // manually bind values from the request
            $form->submit($request->query->get($form->getName()));
        }

        // set filter conditions in user manager
        $userManager->setFilterConditions($form);

        // get material list from manager service 
        $materials = $userManager->getListUsers($roomId);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $materials,
            $request->query->getInt('page', 1),
            10
        );

        return array(
            'roomId' => $roomId,
            'pagination' => $pagination,
            'form' => $form->createView()
        );
    }
    
    /**
     * @Route("/room/{roomId}/user/{itemId}")
     * @Template()
     */
    public function indexAction($roomId, $itemId, Request $request)
    {
        // get room user list
        $userService = $this->get("commsy.user_service");
        $user = $userService->getUser($itemId);
        
        return array(
            'user' => $user
        );
    }
    
    /**
     * @Route("/room/{roomId}/user/{userId}/image")
     */
    public function imageAction($roomId, $userId)
    {
        $userService = $this->get('commsy.user_service');
        $user = $userService->getUser($userId);
        
        $file = $user->getPicture();
        $rootDir = $this->get('kernel')->getRootDir().'/../';

        $environment = $this->get("commsy_legacy.environment")->getEnvironment();
        $disc_manager = $environment->getDiscManager();
        $disc_manager->setContextID($roomId);
        $portal_id = $environment->getCurrentPortalID();
        if ( isset($portal_id) and !empty($portal_id) ) {
            $disc_manager->setPortalID($portal_id);
        } else {
            $context_item = $this->getContextItem();
            if ( isset($context_item) ) {
                $portal_item = $context_item->getContextItem();
                if ( isset($portal_item) ) {
                    $disc_manager->setPortalID($portal_item->getItemID());
                    unset($portal_item);
                }
                unset($context_item);
            }
        }
        $filePath = $disc_manager->getFilePath().$file;

        if (file_exists($rootDir.$filePath)) {
            $content = file_get_contents($rootDir.$filePath);
            if (!$content) {
                $kernel = $this->get('kernel');
                $path = $kernel->locateResource('@CommsyBundle/Resources/public/images/user_unknown.gif');          
                $content = file_get_contents($path);
            }
        } else {
            throw $this->createNotFoundException('The requested file does not exist');   
        }
        $response = new Response($content, Response::HTTP_OK, array('content-type' => 'image'));
        
        $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$file);

        $response->headers->set('Content-Disposition', $contentDisposition);
        
        return $response;
    }
}
