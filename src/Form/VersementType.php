<?php

namespace App\Form;

use App\Entity\Versement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class VersementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Montant à deposer'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr' =>  [
                    'placeholder' =>  'Ecrire le montant en lettre'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Versement::class,
        ]);
    }
}
