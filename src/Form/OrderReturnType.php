<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\OrderReturn;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class OrderReturnType extends AbstractType
{
    private $shop;
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->shop = $options['shop'];
        $builder
            ->add('firstOrder', EntityType::class, [
                'label' => false,
                'class'=> Payment::class,
                'placeholder' => 'Selectionner l\'ancienne vente',
                'query_builder' => function(EntityRepository $er){
                  return $er->createQueryBuilder('p')
                            ->innerJoin('p.invoice', 'i')
                            ->innerJoin('i.orders', 'o')
                            ->andWhere('o.shop = :shop')
                            ->setParameter('shop', $this->shop)
                            ->orderBy('p.createdAt', 'DESC')
                            ;
                          
                }
            ])
            ->add('lastOrder', EntityType::class, [
                'label' => false,
                'class'=> Payment::class,
                'placeholder' => 'Selectionner la nouvelle vente',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('p')
                              ->andWhere('p.status = TRUE')
                              ->innerJoin('p.invoice', 'i')
                              ->innerJoin('i.orders', 'o')
                              ->andWhere('o.shop = :shop')
                              ->setParameter('shop', $this->shop)
                              ->orderBy('p.createdAt', 'DESC')
                              ;
                            
                  }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderReturn::class,
            'shop' => null
        ]);
    }
}
