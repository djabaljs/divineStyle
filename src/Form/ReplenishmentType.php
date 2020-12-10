<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\Color;
use App\Entity\Length;
use App\Entity\Product;
use App\Entity\Provider;
use App\Entity\Replenishment;
use Doctrine\ORM\EntityRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ReplenishmentType extends AbstractType
{
    private $products;
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->products = $options['products'];
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choices' => $this->products,
                'label' => false,
                'placeholder' => 'Produit à réapprovisionner',
                'attr' => [
                ]
            ])
            ->add('provider', EntityType::class, [
                'class' => Provider::class,
                'label' => false,
                'placeholder' => 'Fournisseur',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('p')
                              ->where('p.deleted = false')
                    ;
                }
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Replenishment::class,
            'products' => null
            
        ]);
    }
}
