<?php
namespace App\Form\Type\Bibliographic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BiblioDocManagementType extends AbstractType
{
    /**
     * Builds the form.
     * This method is called for each type in the hierarchy starting from the top most type.
     * Type extensions can further modify the form.
     * 
     * @param  FormBuilderInterface $builder The form builder
     * @param  array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translationDomain = 'form';
        $language = $GLOBALS['environment']->_current_portal->_environment->_selected_language;

        $builder
            ->add('document_editor', TextType::class, array(
                'label' => 'editor',
                'translation_domain' => $translationDomain,
                ))
            ->add('document_maintainer', TextType::class, array(
                'label' => 'maintainer',
                'translation_domain' => $translationDomain,
                ))
            ->add('document_release_number', TextType::class, array(
                'label' => 'release number',
                'translation_domain' => $translationDomain,
                ))
        ;

        if($language == 'en'){
            $format = '{format:\'DD/MM/YYYY\'}';
        } else{
            $format = '{format:\'DD.MM.YYYY\'}';
        }

        $builder->add('document_release_date', TextType::class, array(
            'label' => 'url date',
            'translation_domain' => $translationDomain,
            'required' => false,
            'attr' => array(
                'data-uk-datepicker' => $format
            )
        ));
    }

    /**
     * Returns the prefix of the template block name for this type.
     * The block prefix defaults to the underscored short class name with the "Type" suffix removed
     * (e.g. "UserProfileType" => "user_profile").
     * 
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix()
    {
        return 'biblio_docmanagement';
    }

}