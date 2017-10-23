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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SurveyUpload extends AbstractType
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
            ->add('file', FileType::class, array('label' => $this->translator->__('Upload File'), 'required' => true, 'mapped' => false))
            ->add('savedata', CheckboxType::class, array(
                        'label' => $this->translator->__('Yes I understand this will upload my data and make it available for use.'),
                        'mapped' => false,
                        'attr' => ['style' => 'width:200px']))
            ->add('prepost', ChoiceType::class, array(
                            'label' => $this->translator->__('Pre-instruction or Post-instruction'),
                            'choices' => array('Pre-instruction' => '0',
                                'Post-instruction' => '1'),))
            ->add('institution', TextType::class, array('label' => $this->translator->__('Institution'), 'required' => true))
            ->add('course', TextType::class, array('label' => $this->translator->__('Course'), 'required' => true))
            ->add('surveyDate', DateType::class, array('widget' => 'single_text',),
                array('label' => $this->translator->__('The date of the survey.')))
            ->add('add', SubmitType::class, array('label' => $this->translator->__('Submit')));
    }

    public function getName()
    {
        return 'paustianpmcimodule_surveyupload';
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