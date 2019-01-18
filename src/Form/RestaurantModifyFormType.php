<?php

namespace App\Form;

use App\Entity\Restaurant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantModifyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Nom du restaurant']])
            ->add('number', TelType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Numéro de téléphone']])
            ->add('opensAt', TimeType::class, ['widget' => 'single_text', 'attr' => ['class' => 'timepicker modernize', 'readonly' => true]])
            ->add('closesAt', TimeType::class, ['widget' => 'single_text', 'attr' => ['class' => 'timepicker modernize', 'readonly' => true]])
            ->add('categories')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
