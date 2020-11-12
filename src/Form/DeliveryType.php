<?php

namespace App\Form;

use App\Entity\Delivery;
use App\Entity\DeliveryMan;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Adresse de livraison'
                ]
            ])
            ->add('amountPaid', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Montant de livraison'
                ]
            ])
            ->add('recipient', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom du récepteur'
                ]
            ])
            ->add('recipientPhone', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Téléphone du récepteur'
                ]
            ])
            ->add('deliveryMan', EntityType::class, [
                'label' => false,
                'class' => DeliveryMan::class,
                'placeholder' => 'Livreur',
                'attr' => [
                ]
            ])
            ->add('order',EntityType::class, [
                'label' => false,
                'class' => Order::class,
                'choice_label' => 'orderNumber',
                'placeholder' => 'Commande',
                'attr' => [
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Delivery::class,
        ]);
    }
}
