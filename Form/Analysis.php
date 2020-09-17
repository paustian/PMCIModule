<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/30/17
 * Time: 9:47 PM
 */

namespace Paustian\PMCIModule\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Zikula\UsersModule\Api\CurrentUserApi;

class Analysis extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $currentUserApi;

    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        CurrentUserApi $currentUserApi)   {
        $this->translator = $translator;
        $this->currentUserApi = $currentUserApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('survey1', EntityType::class, [
                'label'      => 'First Survey',
                'class'    => 'PaustianPMCIModule:SurveyEntity',
                'choices' => $options['data']['choiceOptions'],
                'choice_label' => function ($survey) {
                    return $survey->getDisplayName();
                },
                'mapped'     => false,
                'required' => false,
            ])
            ->add('file1', FileType::class, array('label' => 'Upload Pre-Survey', 'required' => false, 'mapped' => false))
            ->add('survey2', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label'      => 'Second Survey',
                'multiple'   => false,
                'class'    => 'PaustianPMCIModule:SurveyEntity',
                'choices' => $options['data']['choiceOptions'],
                'choice_label' => function ($survey) {
                    return $survey->getDisplayName();
                },
                'mapped'     => false,
                'required' => false,
            ])
            ->add('file2', FileType::class, array('label' => 'Upload Post-Survey', 'required' => false, 'mapped' => false))
            ->add('match', CheckboxType::class, [
                    'label' => 'Only analyze students present in each survey.',
                    'mapped' => false,
                    'required' => false
            ])
            ->add('lgstudents', CheckboxType::class, [
                'label' => 'Calculate learning gains for students.',
                'mapped' => false,
                'required' => false
            ])
            ->add('lgtest', CheckboxType::class, [
                'label' => 'Calculate learning gains between tests.',
                'mapped' => false,
                'required' => false
            ])

            ->add('pbc', CheckboxType::class, [
                'label' => 'Calculate point biserial corellations for each item.',
                'mapped' => false,
                'required' => false
            ])
            ->add('discrim', CheckboxType::class, [
                'label' => 'Calculate item discrimination for each test item.',
                'mapped' => false,
                'required' => false
            ])
            ->add('sex', ChoiceType::class, [
                'label' => 'Sex',
                'choices' => ['All' => '0',
                    'Male' => '1',
                    'Female' => '2',],])
            ->add('race', ChoiceType::class, [
                'label' => 'Ethinicity',
                'choices' => ['All' => '0',
                    'American Indian/Alaskan Native' => '1',
                    'Black or African American' => '2',
                    'Asian or Pacific Islander' => '3',
                    'Hispanic/Latino' => '4',
                    'White' => '5',],])
            ->add('esl', ChoiceType::class, [
                'label' => 'English as a Second Language',
                'choices' => ['No' => '0',
                    'Yes' => '1',],])

            ->add('ageOpt', ChoiceType::class, [
                'label' => 'Pick an age operator',
                'choices' => ['=' => '0',
                    '>' => '1',
                    '<' => '2',],
                'required' => false,])
            ->add('age', ChoiceType::class, [
                    'label' => 'Pick an age',
                    'required' => false,
                    'choices' => ['All' => '0',
                        '18-20' => '1',
                        '21-25' => '2',
                        '26-30' => '3',
                        '31-35' => '4',
                        '36-40' => '5',
                        '41-45' => '6',
                        '46-55' => '7',
                        '56-65' => '8',
                        '>65' => '9',],
                    ])

            ->add('gpa', ChoiceType::class, [
                'label' => 'Pick a gpa',
                'choices' => ['> 3.5'=> '1',
                            '3.0-3.49' => '2',
                            '2.5-3.0' => '3',
                            '2.0-2.5' => '4',
                            '< 2.0' => '5',],
                'required' => false,])

            ->add('add', SubmitType::class, array('label' => 'Submit'));

    }

    public function getBlockPrefix()
    {
        return 'paustianpmcimodule_analysis';
    }
}