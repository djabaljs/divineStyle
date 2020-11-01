<?php

namespace App\Form;

use App\Entity\Product;
use App\Repository\ProductRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductType extends AbstractType
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }   

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', EntityType::class, [
                'label' => false,
                'class' => Product::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;

        $builder->addEventListener(
        	FormEvents::PRE_SUBMIT,
	        function (FormEvent $event) {
	            $data = $event->getData();
	            $form = $event->getForm();

                $product = $this->productRepository->find($data['name']);
                dd($product);
                // foreach ($event->getForm() as $customField) {
                    $form
                        ->get('name')->getParent()
                        ->add('price', IntegerType::class, [
                            'label' => false,
                            'attr' => [
                                'readonly' => true,
                                'value' => $product->getPrice()
                            ]
                        ])
                        ->add('quantity', IntegerType::class, [
                            'label' => false,

                        ])
                    ;
                }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = array(
            'compound' => true,
            'inherit_data' => true,
        );

        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ProductType';
    }
}
