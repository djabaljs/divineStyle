<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ShopType extends AbstractType
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
            ->add('phone', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Téléphone du magasin'
                ]
            ])
            ->add('manager', EntityType::class, [
                'label' => false,
                'class' => User::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('q')
                        ->where('q.roles LIKE :roles')
                        ->setParameter('roles', '%"ROLE_MANAGER"%')
                        ->andWhere('q.shops IS NULL')
                        ->orderBy('q.id', 'DESC');

                },
       
            ])
            // ->add('staffs', EntityType::class, [
            //     'label' => false,
            //     'multiple' => true,
            //     'required'=>false,
            //     'mapped'=>false,
            //     'class' => User::class,
            //     'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
            //         return $er->createQueryBuilder('q')
            //             ->where('q.roles LIKE :roles')
            //             ->setParameter('roles', '%"ROLE_STAFF"%')
            //             ->andWhere('q.shops IS NULL')
            //             ->orderBy('q.id', 'DESC');
            //     }
            // ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
        ]);
    }
}
