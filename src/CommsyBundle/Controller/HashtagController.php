<?php

namespace CommsyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

use CommsyBundle\Form\Type\HashtagEditType;
use CommsyBundle\Form\Type\HashtagMergeType;
use CommsyBundle\Entity\Labels;

class HashtagController extends Controller
{
    /**
     * @Template("CommsyBundle:Hashtag:show.html.twig")
     */
    public function showAction($roomId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $hashtags = $em->getRepository('CommsyBundle:Labels')
            ->findRoomHashtags($roomId);

        return array(
            'hashtags' => $hashtags
        );
    }

    /**
     * @Template("CommsyBundle:Hashtag:showDetail.html.twig")
     */
    public function showDetailAction($roomId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $hashtags = $em->getRepository('CommsyBundle:Labels')
            ->findRoomHashtags($roomId);

        return array(
            'hashtags' => $hashtags
        );
    }

    /**
     * @Template("CommsyBundle:Hashtag:showDetailShort.html.twig")
     */
    public function showDetailShortAction($roomId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $hashtags = $em->getRepository('CommsyBundle:Labels')
            ->findRoomHashtags($roomId);

        return array(
            'hashtags' => $hashtags
        );
    }

    /**
     * @Route("/room/{roomId}/hashtag/edit/{labelId}")
     * @Template()
     */
    public function editAction($roomId, $labelId = null, Request $request)
    {
        $legacyEnvironment = $this->get('commsy_legacy.environment')->getEnvironment();

        $roomManager = $legacyEnvironment->getRoomManager();
        $roomItem = $roomManager->getItem($roomId);

        if (!$roomItem->withBuzzwords()) {
            throw $this->createAccessDeniedException('The requested room does not have hashtags enabled.');
        }

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('CommsyBundle:Labels');

        if ($labelId) {
            $hashtag = $repository->findOneByItemId($labelId);
        } else {
            $hashtag = new Labels();
            $hashtag->setContextId($roomId);
            $hashtag->setType('buzzword');
        }

        $editForm = $this->createForm(HashtagEditType::class, $hashtag);


        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            // persist changes / delete hashtag
            $labelManager = $legacyEnvironment->getLabelManager();

            if ($editForm->has('delete') && $editForm->get('delete')->isClicked()) {
                $buzzwordItem = $labelManager->getItem($hashtag->getItemId());
                $buzzwordItem->delete();
            }

            if ($editForm->has('new') && $editForm->get('new')->isClicked()) {
                $buzzwordItem = $labelManager->getNewItem();

                $buzzwordItem->setLabelType('buzzword');
                $buzzwordItem->setContextID($hashtag->getContextId());
                $buzzwordItem->setCreatorItem($legacyEnvironment->getCurrentUserItem());
                $buzzwordItem->setName($hashtag->getName());

                $buzzwordItem->save();
            }

            if ($editForm->has('update') && $editForm->get('update')->isClicked()) {
                $buzzwordItem = $labelManager->getItem($hashtag->getItemId());

                $buzzwordItem->setName($hashtag->getName());

                $buzzwordItem->save();
            }

            return $this->redirectToRoute('commsy_hashtag_edit', [
                'roomId' => $roomId,
            ]);
        }

        $hashtags = $repository->findRoomHashtags($roomId);

        $mergeForm = $this->createForm(HashtagMergeType::class, null, ['roomId'=>$roomId]);

        $mergeForm->handleRequest($request);
        if ($mergeForm->isValid()) {
            // persist changes / delete hashtag
            $labelManager = $legacyEnvironment->getLabelManager();

            die('exit');

            

            return $this->redirectToRoute('commsy_hashtag_edit', [
                'roomId' => $roomId,
            ]);
        }

        return [
            'editForm' => $editForm->createView(),
            'roomId' => $roomId,
            'hashtags' => $hashtags,
            'labelId' => $labelId,
            'mergeForm' => $mergeForm->createView(),
        ];
    }
}
