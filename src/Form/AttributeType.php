<?php

namespace App\Form;

use App\Entity\Color;
use App\Entity\Length;
use App\Entity\Attribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        if($options['color'] == 1){
            $builder
            ->add('name', TextType::class, [
                'label' => false
            ])
            ->add('visible', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('variation', CheckboxType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('colors', EntityType::class, [
                'label' => false,
                'class' => Color::class,
                'required'=>false,
                'multiple' => true,

            ])
        ;
        }else{
            $builder
            ->add('name', TextType::class, [
                'label' => false
            ])
            ->add('visible', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('variation', CheckboxType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('lengths', EntityType::class, [
                'label' => false,
                'class' => Length::class,
                'multiple' => true,
                'required'=>false,
                'mapped'=>true,

            ])
        ;
        }
     
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Attribute::class,
            'color' => null,
        ]);
    }
}
