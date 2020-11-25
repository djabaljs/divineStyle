<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Delivery;
use App\Entity\DeliveryMan;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DeliveryType extends AbstractType
{
    private $shop;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->shop = $options['shop'];

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
            ->add('status', CheckboxType::class, [
                'label' => false,
                'required' => false,
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
                'query_builder' => function (EntityRepository $er){
                    $qb =  $er->createQueryBuilder('o');
                    $qb
                        ->where('o.shop = :shop')
                        ->setParameter('shop', $this->shop)
                       ->orderBy('o.createdAt', 'DESC');

                    return $qb;
                },
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
            'shop' => null
        ]);
    }
}
