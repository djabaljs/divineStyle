<?php

namespace App\Form;

use App\Entity\Fund;
use App\Entity\TransactionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('object', TextareaType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Objet de l\'opération'
                ]
            ])
            ->add('amount', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Montant de l\'opération'
                ]
            ])
            ->add('transactionType', EntityType::class, [
                'label' => false,
                'class' => TransactionType::class,
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Fund::class,
        ]);
    }
}
