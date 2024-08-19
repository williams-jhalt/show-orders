<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('vendorNumber')
                ->add('company')
                ->add('email', EmailType::class)
                ->add('sponsershipLevel', ChoiceType::class, [
                    'placeholder' => 'none',
                    'required' => false,
                    'choices' => [
                        'Gold' => 'Gold',
                        'Silver' => 'Silver',
                        'Bronze' => 'Bronze'
                    ]
                ])
                ->add('booth', TextType::class, ['required' => false])
                ->add('imageUrl', FileType::class, ['required' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Vendor'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'appbundle_vendor';
    }

}
