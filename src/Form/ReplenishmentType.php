<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\Product;
use App\Entity\Provider;
use App\Entity\Replenishment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ReplenishmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => false,
                'placeholder' => 'Produit à réapprovisionner',
                'attr' => [
                ]
            ])
            ->add('provider', EntityType::class, [
                'class' => Provider::class,
                'label' => false,
                'placeholder' => 'Fournisseur',

                'attr' => [
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Replenishment::class
        ]);
    }
}
