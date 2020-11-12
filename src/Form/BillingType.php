<?php

namespace App\Form;

use App\Entity\Billing;
use App\Entity\Customer;
use App\Entity\DeliveryMan;
use App\Entity\PaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BillingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paymentType', EntityType::class, [
                'class' => PaymentType::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Selectionner la méthode de paiement',
                'label' => false
            ])
            ->add('deliveryMan', EntityType::class, [
                'class' => DeliveryMan::class,
                'label' => false,
                'placeholder' => 'Selectionner un livreur',
                'required' => false,
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'label' => false,
                'placeholder' => 'Selectionner un client',
            ])
            ->add('amountPaid', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Montant à payer',
                ]
            ])
            ->add('deliveryAmount', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Montant de livraison',
                ]
            ])
            ->add('recipient', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Nom du destinataire',
                ]
            ])
            ->add('recipientPhone', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Téléphone du destinataire'
                ]
               
            ])
            ->add('choice', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'placeholder' => 'Sélectionner un destinataire',
                'choices'  => [
                    'Moi même' => 0,
                    'Autre' => 1,
                ],
            ])
            ->add('deliveryAddress', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Adresse de livraison',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Billing::class,
        ]);
    }
}
