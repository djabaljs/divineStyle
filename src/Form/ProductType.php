<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Height;
use App\Entity\Length;
use App\Entity\Product;
use App\Entity\Provider;
use App\Entity\Shop;
use App\Entity\Width;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,  [
                'label' => false,
            ])
            ->add('buyingPrice', NumberType::class, [
                'label' => false, 
              
            ])
            ->add('sellingPrice', NumberType::class, [
                'label' => false
            ])
            ->add('quantity', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('category', EntityType::class, [
                'label' => false,
                'class' => Category::class, 
                'placeholder' => 'Selectionner une catégorie'
            ])
            ->add('length', EntityType::class, [
                'label' => false,
                'class' => Length::class,
                'required' => false,
                'placeholder' => 'Selectionner la taille'
            ])
            ->add('width', EntityType::class, [
                'label' => false,
                'class' => Width::class,
                'required' => false,
                'placeholder' => 'Selectionner la largeur'

            ])
            ->add('height', EntityType::class, [
                'label' => false,
                'class' => Height::class,
                'required' => false,
                'placeholder' => 'Selectionner la hauteur'

            ])
            ->add('color', EntityType::class, [
                'label' => false,
                'class' => Color::class,
                'required' => false,
                'placeholder' => 'Selectionner la coleur'
            ])
            ->add('minimumStock', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('provider', EntityType::class, [
                'label' => false,
                'class' => Provider::class,
                'placeholder' => 'Selectionner un fournisseur'
            ])
            ->add('shop', EntityType::class, [
                'label' => false,
                'class' => Shop::class,
                'placeholder' => 'Selectionner un magasin'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
