<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('birthDay', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Date d\'anniversaire'
                ],
                // 'widget' => 'single_text',
                // 'input'  => 'datetime_immutable'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
