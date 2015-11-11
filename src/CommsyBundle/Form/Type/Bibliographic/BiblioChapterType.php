<?php
namespace CommsyBundle\Form\Type\Bibliographic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BiblioChapterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translationDomain = 'form';

        $builder
            ->add('editor', 'text', array(
                'label' => 'editor',
                'translation_domain' => $translationDomain,
                ))
            ->add('publishing_date', 'text', array(
                'label' => 'publishing date',
                'translation_domain' => $translationDomain,
                ))
            ->add('address', 'text', array(
                'label' => 'address',
                'translation_domain' => $translationDomain,
                ))
            ->add('edition', 'text', array(
                'label' => 'edition',
                'translation_domain' => $translationDomain,
                ))
            ->add('series', 'text', array(
                'label' => 'series',
                'translation_domain' => $translationDomain,
                ))
            ->add('volume', 'text', array(
                'label' => 'volume',
                'translation_domain' => $translationDomain,
                ))
            ->add('isbn', 'text', array(
                'label' => 'isbn',
                'translation_domain' => $translationDomain,
                ))
            ->add('url', 'text', array(
                'label' => 'url',
                'translation_domain' => $translationDomain,
                ))
            ->add('url_date', 'date', array(
                'label' => 'url date',
                'translation_domain' => $translationDomain,
                ))
        ;
    }

    public function getName()
    {
        return 'biblio_chapter';
    }

}