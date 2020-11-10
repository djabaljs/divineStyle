<?php

namespace App\Form;

use App\Entity\Attribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false
            ])
            ->add('position', IntegerType::class, [
                'label' => false,
                'attr' =>  [
                    'min' => 0,
                    'max' => 5
                ]
            ])
            ->add('visible', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('variation', CheckboxType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('text', TextareaType::class, [
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Attribute::class,
        ]);
    }
}
