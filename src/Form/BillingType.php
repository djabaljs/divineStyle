<?php

namespace App\Form;

use App\Entity\Billing;
use App\Entity\Customer;
use App\Entity\DeliveryMan;
use App\Entity\PaymentType;
use Doctrine\ORM\EntityRepository;
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
    private $shop;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->shop = $options['shop'];

        $builder
            ->add('paymentType', EntityType::class, [
                'class' => PaymentType::class,
                'choice_label' => 'name',
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
                'query_builder' => function (EntityRepository $er){
                    $qb = $er->createQueryBuilder('c');
                    $qb 
                        ->where('c.shops = :shop')
                        ->setParameter('shop', $this->shop)
                        ;
                    return $qb;
                },
                'label' => false,
                'required' => false,
                'placeholder' => 'Selectionner un client',
            ])
            // ->add('amountPaid', IntegerType::class, [
            //     'label' => false,
            //     'required' => false,
            //     'attr' => [
            //     'placeholder' => 'Montant à payer',
            //     ]
            // ])
            ->add('deliveryAmount', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Montant de livraison',
                ]
            ])
            ->add('discount', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Remise',
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
            ->add('customerType', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'placeholder' => 'Sélectionner un type client',
                'choices'  => [
                    'Ancien client' => 0,
                    'Nouveau client' => 1,
                ],
            ])
            ->add('customerFistname', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Nom du client',
                ]
            ])
            ->add('customerLastname', TextType::class, [
                'label' => false,
                'attr' => [
                'placeholder' => 'Prénom du client',
                ]
            ])
            ->add('customerPhone', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Téléphone du client',
                ]
            ])
            ->add('customerEmail', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Email du client',
                ]
            ])
            ->add('customerBirthDay', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                'placeholder' => 'Date d\'anniversaire du client',
                ]
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
            'shop' => null
        ]);
    }
}
