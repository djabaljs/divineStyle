<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\Color;
use App\Entity\Length;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Provider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SimpleAdminProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class,  [
            'label' => false,
        ])
        // ->add('buyingPrice', NumberType::class, [
        //     'label' => false,
        //     'required' => false
        // ])
        ->add('sellingPrice', NumberType::class, [
            'label' => false,
        ])
        ->add('onSaleAmount', NumberType::class, [
            'label' => false,
            'required' => false,
        ])
        // ->add('quantity', IntegerType::class, [
        //     'label' => false,
        //     'attr' => [
        //         'min' => 0,
        //         'value' => 1
        //     ],
        //     'required' => true
        // ])
        ->add('category', EntityType::class, [
            'label' => false,
            'required' => true,
            'class' => Category::class, 
            'placeholder' => 'Selectionner une catégorie'
        ])
        ->add('lengths', EntityType::class, [
            'label' => false,
            'class' => Length::class,
            'multiple' => true,
            'required'=>false,
            'mapped'=>true,

        ])
        ->add('colors', EntityType::class, [
            'label' => false,
            'class' => Color::class,
            'required'=>false,
            'multiple' => true,

        ])
        ->add('minimumStock', IntegerType::class, [
            'label' => false,
            'required' => true,
            'attr' => [
                'min' => 0,
                'value' => 2
            ]
        ])
        ->add('isVariable', ChoiceType::class, [
            'label' => false,
            'choices'  => [
                'Simple' => 0,
                'Variable' => 1,
            ],
        ])
        ->add('provider', EntityType::class, [
            'label' => false,
            'class' => Provider::class,
            'placeholder' => 'Selectionner un fournisseur',
            'query_builder' => function(EntityRepository $er){
                return $er->createQueryBuilder('p')
                          ->where('p.deleted = FALSE')
                ;
            }
        ])
        // ->add('shop', EntityType::class, [
        //     'label' => false,
        //     'class' => Shop::class,
        //     'placeholder' => 'Selectionner un magasin'
        // ])
    ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
