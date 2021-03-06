<?php
namespace Paustian\PMCIModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('name', TextType::class, array('label' => 'Name', 'required' => true))
            ->add('email', EmailType::class, array('label' => 'Email', 'required' => true))
            ->add('institution', TextType::class, array('label' => 'Institution', 'required' => true))
            ->add('course', TextType::class, array('label' => 'Course', 'required' => true))
            ->add('add', SubmitType::class, array('label' => 'Submit'));
        
    }

    public function getName()
    {
        return 'paustianpmcimodule_person';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\PMCIModule\Entity\PersonEntity',
        ));
    }
}

