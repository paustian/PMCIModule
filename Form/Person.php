<?php
namespace Paustian\PMCIModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * Description of ExamForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 * 
 */
class Person extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => __('Name'), 'required' => true))
            ->add('email', EmailType::class, array('label' => __('Email'), 'required' => true))
            ->add('institution', TextType::class, array('label' => __('Institution'), 'required' => true))
            ->add('course', TextType::class, array('label' => __('Course'), 'required' => true))
            ->add('add', 'submit', array('label' => 'Submit'));
        
    }

    public function getName()
    {
        return 'paustianpmcimodule_person';
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
            'data_class' => 'Paustian\PMCIModule\Entity\PersonEntity',
        ));
    }
}

