<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ShopUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
            ])
            ->add('address', TextType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('phone', NumberType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('manager', EntityType::class, [
                'label' => false,
                'class' => User::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :roles')
                        ->setParameter('roles', '%"ROLE_MANAGER"%')
                        ->orderBy('u.id', 'ASC');
                },
       
            ]);
            // ->add('staffs', EntityType::class, [
            //     'class' => User::class,
            //     'label' => false,
            //     'multiple' => true,
            //     'required'=>false,
            //     'mapped'=>true,
            //     'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
            //         return $er->createQueryBuilder('u')
            //             ->where('u.roles LIKE :roles')
            //             ->setParameter('roles', '%"ROLE_STAFF"%')
            //             ->orderBy('u.id', 'ASC');
            //     }
            // ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
        ]);
    }
}
