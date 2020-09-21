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
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;
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
            ->add('file', FileType::class,
                ['label' => 'Upload File',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new File(['maxSize' => '10240k', 'mimeTypes' => ['text/csv']]),
                    'mimeTypeMessage' => 'Please make sure you are uploading a text, csv file'
                    ]
                ])
            ->add('savedata', CheckboxType::class, [
                        'label' => 'I understand that uploading my data will make it available to others',
                        'mapped' => false])
            ->add('prepost', ChoiceType::class, [
                            'label' => 'Pre-instruction or Post-instruction',
                            'choices' => ['Pre-instruction' => '0',
                                'Post-instruction' => '1'],])
            ->add('institution', TextType::class, ['label' => 'Institution', 'required' => true])
            ->add('course', TextType::class, array('label' => 'Course', 'required' => true))
            ->add('surveyDate', DateType::class, ['widget' => 'single_text', 'label' => 'The date of the survey.'])
            ->add('add', SubmitType::class, ['label' => 'Submit']);
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