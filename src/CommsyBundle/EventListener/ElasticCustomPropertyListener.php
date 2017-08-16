<?php

namespace CommsyBundle\EventListener;

use Commsy\LegacyBundle\Services\LegacyEnvironment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use FOS\ElasticaBundle\Event\TransformEvent;

class ElasticCustomPropertyListener implements EventSubscriberInterface
{
    private $legacyEnvironment;

    public function __construct(LegacyEnvironment $legacyEnvironment)
    {
        $this->legacyEnvironment = $legacyEnvironment->getEnvironment();
    }

    public static function getSubscribedEvents()
    {
        return [
            TransformEvent::POST_TRANSFORM => 'addCustomProperty'
        ];
    }

    public function addCustomProperty(TransformEvent $event)
    {
        $fields = $event->getFields();

        if (isset($fields['hashtags'])) {
            $this->addHashtags($event);
        }

        if (isset($fields['tags'])) {
            $this->addTags($event);
        }

        if (isset($fields['annotations'])) {
            $this->addAnnotations($event);
        }

        if (isset($fields['files'])) {
            $this->addFilesContent($event);
        }

        if (isset($fields['discussionarticles'])) {
            $this->addDiscussionArticles($event);
        }

        if (isset($fields['steps'])) {
            $this->addSteps($event);
        }

        if (isset($fields['sections'])) {
            $this->addSections($event);
        }
    }

    private function addHashtags(TransformEvent $event)
    {
        $itemManager = $this->legacyEnvironment->getItemManager();
        $item = $itemManager->getItem($event->getObject()->getItemId());

        $hashtags = $item->getBuzzwordList();
        if ($hashtags->isNotEmpty()) {
            $objectHashtags = [];

            $hashtag = $hashtags->getFirst();
            while ($hashtag) {
                if (!$hashtag->isDeleted()) {
                    $objectHashtags[] = $hashtag->getName();
                }

                $hashtag = $hashtags->getNext();
            }

            if (!empty($objectHashtags)) {
                $event->getDocument()->set('hashtags', $objectHashtags);
            }
        }
    }

    private function addTags(TransformEvent $event)
    {
        $itemManager = $this->legacyEnvironment->getItemManager();
        $item = $itemManager->getItem($event->getObject()->getItemId());

        $tags = $item->getTagList();
        if ($tags->isNotEmpty()) {
            $objectTags = [];

            $tag = $tags->getFirst();
            while ($tag) {
                if (!$tag->isDeleted()) {
                    $objectTags[] = $tag->getTitle();
                }

                $tag = $tags->getNext();
            }

            if (!empty($objectTags)) {
                $event->getDocument()->set('tags', $objectTags);
            }
        }
    }

    private function addAnnotations(TransformEvent $event)
    {
        $itemManager = $this->legacyEnvironment->getItemManager();
        $item = $itemManager->getItem($event->getObject()->getItemId());

        $annotations = $item->getAnnotationList();
        if ($annotations->isNotEmpty()) {
            $objectTags = [];

            $annotation = $annotations->getFirst();
            while ($annotation) {
                if (!$annotation->isDeleted()) {
                    $objectTags[] = $annotation->getDescription();
                }

                $annotation = $annotations->getNext();
            }

            if (!empty($objectTags)) {
                $event->getDocument()->set('annotations', $objectTags);
            }
        }
    }

    private function addFilesContent(TransformEvent $event)
    {
        $itemManager = $this->legacyEnvironment->getItemManager();
        $item = $itemManager->getItem($event->getObject()->getItemId());

        $files = $item->getFileList();
        if ($files->isNotEmpty()) {
            $fileContents = [];

            $file = $files->getFirst();
            while ($file) {
                if (!$file->isDeleted()) {
                    $content = $file->getContentBase64();
                    if (!empty($content)) {
                        $fileContents[] = $content;
                    }
                }

                $file = $files->getNext();
            }

            if (!empty($fileContents)) {
                $event->getDocument()->set('files', $fileContents);
            }
        }
    }

    public function addDiscussionArticles($event)
    {
        $discussionManager = $this->legacyEnvironment->getDiscussionManager();
        $discussion = $discussionManager->getItem($event->getObject()->getItemId());

        $articles = $discussion->getAllArticles();
        if ($articles->isNotEmpty()) {
            $articleContents = [];

            $article = $articles->getFirst();
            while ($article) {
                if (!$article->isDeleted() && !$article->isDraft()) {
                    $articleContents[] = [
                        'subject' => $article->getSubject(),
                        'description' => $article->getDescription(),
                    ];
                }

                $article = $articles->getNext();
            }

            if (!empty($articleContents)) {
                $event->getDocument()->set('discussionarticles', $articleContents);
            }
        }
    }

    public function addSteps($event)
    {
        $todoManager = $this->legacyEnvironment->getTodoManager();
        $todo = $todoManager->getItem($event->getObject()->getItemId());

        $steps = $todo->getStepItemList();
        if ($steps->isNotEmpty()) {
            $stepContents = [];

            $step = $steps->getFirst();
            while ($step) {
                if (!$step->isDeleted() && !$step->isDraft()) {
                    $stepContents[] = [
                        'title' => $step->getTitle(),
                        'description' => $step->getDescription(),
                    ];
                }

                $step = $steps->getNext();
            }

            if (!empty($stepContents)) {
                $event->getDocument()->set('steps', $stepContents);
            }
        }
    }

    public function addSections($event)
    {
        $materialManager = $this->legacyEnvironment->getMaterialManager();
        $material = $materialManager->getItem($event->getObject()->getItemId());

        $sections = $material->getSectionList();
        if ($sections->isNotEmpty()) {
            $sectionContents = [];

            $section = $sections->getFirst();
            while ($section) {
                if (!$section->isDeleted() && !$section->isDraft()) {
                    $sectionContents[] = [
                        'title' => $section->getTitle(),
                        'description' => $section->getDescription(),
                    ];
                }

                $section = $sections->getNext();
            }

            if (!empty($sectionContents)) {
                $event->getDocument()->set('steps', $sectionContents);
            }
        }
    }
}