<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('price', NumberType::class, [
                'label' => false,
                'scale' => 2,
                'attr' => ['class' => 'form-control'],
            ])
            // Ajout des champs pour chaque taille (XS à XL)
            ->add('stock_0', NumberType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('stock_1', NumberType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('stock_2', NumberType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('stock_3', NumberType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('stock_4', NumberType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('highlighted', CheckboxType::class, [
                'label' => 'M\'être en avant',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}