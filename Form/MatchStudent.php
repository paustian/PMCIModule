<?php
namespace Paustian\PMCIModule\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class MatchStudent extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('survey1', EntityType::class, [
                'label'      => 'Pre-Survey',
                'class'    => 'PaustianPMCIModule:SurveyEntity',
                'choices' => $options['data']['choiceOptions'],
                'choice_label' => function ($survey) {
                    return $survey->getDisplayName();
                },
                'mapped'     => false,
                'required' => true,
            ])
            ->add('survey2', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label'      => 'Post-Survey',
                'multiple'   => false,
                'class'    => 'PaustianPMCIModule:SurveyEntity',
                'choices' => $options['data']['choiceOptions'],
                'choice_label' => function ($survey) {
                    return $survey->getDisplayName();
                },
                'mapped'     => false,
                'required' => true,
            ])
            ->add('add', SubmitType::class, array('label' => 'Submit'));

    }

    public function getBlockPrefix()
    {
        return 'paustianpmcimodule_matchstudent';
    }
}