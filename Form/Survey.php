<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/26/17
 * Time: 11:32 AM
 */

namespace Paustian\PMCIModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class Survey extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator)   {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prepost', ChoiceType::class, array(
                'label' => $this->translator->__('Pre-instruction or Post-instruction'),
                'choices' => array('0' => 'Pre-instruction',
                    '1' => 'Post-instruction' ),))
            ->add('institution', TextType::class, array('label' => $this->translator->__('Institution'), 'required' => true))
            ->add('course', TextType::class, array('label' => $this->translator->__('Course'), 'required' => true))
            ->add('surveyDate', DateType::class, array('widget' => 'single_text',),
                array('label' => $this->translator->__('The date of the survey.')))
            ->add('add', SubmitType::class, array('label' => $this->translator->__('Submit')));
    }

    public function getName()
    {
        return 'paustianpmcimodule_survey';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\PMCIModule\Entity\SurveyEntity',
        ));
    }
}