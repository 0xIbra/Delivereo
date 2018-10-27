<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressIncludeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('line1', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Adresse']])
            ->add('line2', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Complément, Bâtiment, Appartement...']])
            ->add('city', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Ville']])
            ->add('zipCode', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Code postal']])

//            ->add('city', TextType::class)
//            ->add('zipCode', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
