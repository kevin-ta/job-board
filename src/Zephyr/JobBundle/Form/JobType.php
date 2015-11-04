<?php

namespace Zephyr\JobBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                    'label' => 'Titre'))
            ->add('category', 'entity', array(
                    'class'    => 'ZephyrJobBundle:Category',
                    'choice_label' => 'name',
                    'multiple' => false,
                    'expanded' => false,
                    'label' => 'Catégorie'))
            ->add('short_content', 'text', array(
                    'attr' => array('placeholder' => 'Pas plus de 20 mots'),
                    'label' => 'Aperçu'))
            ->add('content', 'textarea', array(
                    'attr' => array('style' => 'height: 200px'),
                    'label' => "Texte de l'annonce"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Zephyr\JobBundle\Entity\Job',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'job';
    }
}