<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\Color;
use App\Entity\Length;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Provider;
use App\Entity\ProductOptionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ProductUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,  [
                'label' => false,
            ])
            ->add('buyingPrice', NumberType::class, [
                'label' => false,
                'required' => false

              
            ])
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
            // ->add('width', EntityType::class, [
            //     'label' => false,
            //     'class' => Width::class,
            //     'required' => false,
            //     'placeholder' => 'Selectionner la largeur'

            // ])
            // ->add('height', EntityType::class, [
            //     'label' => false,
            //     'class' => Height::class,
            //     'required' => false,
            //     'placeholder' => 'Selectionner la hauteur'

            // ])
            ->add('minimumStock', IntegerType::class, [
                'label' => false,
                'required' => true,
            ])
           
            ->add('provider', EntityType::class, [
                'label' => false,
                'class' => Provider::class,
                'placeholder' => 'Selectionner un fournisseur'
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
