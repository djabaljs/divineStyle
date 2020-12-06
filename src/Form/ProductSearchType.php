<?php

namespace App\Form;

use App\Entity\ProductSearch;
use App\Entity\Shop;
use App\Entity\Color;
use App\Entity\Length;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shop', EntityType::class, [
                'label' => false,
                'class' => Shop::class,
                'placeholder' => 'Boutique',
                'required' => false,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('s')
                               ->where('s.deleted = false')
                    ;
                }
            ])
            ->add('product', EntityType::class, [
                'label' => false,
                'class' => Product::class,
                'placeholder' => 'Produit',
                'required' => false,
                'choices' => $options['products']
            ])
            ->add('color', EntityType::class, [
                'label' => false,
                'class' => Color::class,
                'placeholder' => 'Couleur',
                'choice_label' => 'name',
                'required' => false,

            ])
            ->add('length', EntityType::class, [
                'label' => false,
                'class' => Length::class,
                'placeholder' => 'Taille',
                'choice_label' => 'name',
                'required' => false,

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductSearch::class,
            'products' => null
        ]);
    }
}
