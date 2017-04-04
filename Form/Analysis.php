<?php
/**
 * Created by PhpStorm.
 * User: paustian
 * Date: 3/30/17
 * Time: 9:47 PM
 */

namespace Paustian\PMCIModule\Form;

use Doctrine\DBAL\Types\FloatType;
use Paustian\PMCIModule\Entity\SurveyEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class Analysis extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('survey1', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label'      => __('First Survey'),
                'choice_label'   => false,
                'class'    => 'PaustianPMCIModule:SurveyEntity',
                'choice_label' => function ($survey) {
                    return $survey->getDisplayName();
                },
                'mapped'     => false,
                'required' => false,
            ])
            ->add('file1', FileType::class, array('label' => __('Upload Pre-Survey'), 'required' => false, 'mapped' => false))
            ->add('survey2', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label'      => __('Second Survey'),
                'multiple'   => false,
                'class'    => 'PaustianPMCIModule:SurveyEntity',
                'choice_label' => function ($survey) {
                    return $survey->getDisplayName();
                },
                'mapped'     => false,
                'required' => false,
            ])
            ->add('file2', FileType::class, array('label' => __('Upload Post-Survey'), 'required' => false, 'mapped' => false))
            ->add('match', CheckboxType::class, [
                    'label' => __('Only analyze students present in each survey.'),
                    'mapped' => false,
                    'required' => false
            ])
            ->add('lgstudents', CheckboxType::class, [
                'label' => __('Calculate learning gains for students.'),
                'mapped' => false,
                'required' => false
            ])
            ->add('lgtest', CheckboxType::class, [
                'label' => __('Calculate learning gains between tests.'),
                'mapped' => false,
                'required' => false
            ])
            ->add('diff', CheckboxType::class, [
                'label' => __('Calculate item difficulty for each item.'),
                'mapped' => false,
                'required' => false
            ])
            ->add('pbc', CheckboxType::class, [
                'label' => __('Calculate point biserial corellations for each item.'),
                'mapped' => false,
                'required' => false
            ])
            ->add('discrim', CheckboxType::class, [
                'label' => __('Calculate item discrimination for each test item.'),
                'mapped' => false,
                'required' => false
            ])
            ->add('sex', ChoiceType::class, [
                'label' => __('Sex'),
                'choices' => ['0' => 'All',
                    '1' => 'Male',
                    '2' => 'Female',],])
            ->add('race', ChoiceType::class, [
                'label' => __('Ethinicity'),
                'choices' => ['0' => 'All',
                    '1' => 'American Indian/Alaskan Native',
                    '2' => 'Black or African American',
                    '3' => 'Asian or Pacific Islander',
                    '4' => 'Hispanic/Latino',
                    '5' => 'White',],])
            ->add('esl', ChoiceType::class, [
                'label' => __('English as a Second Language'),
                'choices' => ['0' => 'No',
                    '1' => 'Yes',],])

            ->add('ageOpt', ChoiceType::class, [
                'label' => __('Pick an age operator'),
                'choices' => ['0' => '=',
                    '1' => '>',
                    '2' => '<',],
                'required' => false,])
            ->add('age', ChoiceType::class, [
                    'label' => __('Pick an age'),
                    'required' => false,
                    'choices' => ['0' => 'All',
                        '1' => '18-20',
                        '2' => '21-25',
                        '3' => '26-30',
                        '4' => '31-35',
                        '5' => '36-40',
                        '6' => '41-45',
                        '7' => '46-55',
                        '8' => '56-65',
                        '9' => '>65',],
                    ])

            ->add('gpa', ChoiceType::class, [
                'label' => __('Pick a gpa'),
                'required' => false,
                'choices' => ['1'=> '> 3.5',
                            '2' => '3.0-3.49',
                            '3' => '2.5-3.0',
                            '4' => '2.0-2.5',
                            '5' => '< 2.0',],
                'required' => false,])

            ->add('add', 'submit', array('label' => 'Submit'));

    }

    public function getName()
    {
        return 'paustianpmcimodule_analysis';
    }
}