<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\OrderSearch;
use App\Entity\PaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class OrderSearchByShopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shop', EntityType::class, [
                'class' =>  Shop::class,
                'choice_label' => 'name',
                'placeholder' => 'Magasin',
                'label' => false,
                'required' => false,
            ])
            ->add('start', DateType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Date debut'
                ],
                'widget' => 'single_text',
                'input'  => 'datetime_immutable'
            ])
            ->add('end', DateType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Date fin'
                ],
                'widget' => 'single_text',
                'input'  => 'datetime_immutable'
            ])
            ->add('paymentType', EntityType::class, [
                'class' =>  PaymentType::class,
                'choice_label' => 'name',
                'placeholder' => 'Type de paiment',
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderSearch::class
        ]);
    }
}
