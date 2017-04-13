<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/24/17
 * Time: 7:23 PM
 */

namespace Paustian\PMCIModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SurveyUpload extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array('label' => __('Upload File'), 'required' => true, 'mapped' => false))
            ->add('savedata', CheckboxType::class, array(
                        'label' => __('Yes I understand this will upload my data and make it available for use.'),
                        'mapped' => false,
                        'attr' => ['style' => 'width:200px']))
            ->add('prepost', ChoiceType::class, array(
                            'label' => __('Pre-instruction or Post-instruction'),
                            'choices' => array('0' => 'Pre-instruction',
                                            '1' => 'Post-instruction' ),))
            ->add('institution', TextType::class, array('label' => __('Institution'), 'required' => true))
            ->add('course', TextType::class, array('label' => __('Course'), 'required' => true))
            ->add('surveyDate', DateType::class, array('widget' => 'single_text',),
                array('label' => __('The date of the survey.')))
            ->add('add', 'submit', array('label' => 'Submit'));
    }

    public function getName()
    {
        return 'paustianpmcimodule_surveyupload';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\PMCIModule\Entity\SurveyEntity',
        ));
    }
}