<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Restaurant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Nom du restaurant']])
            ->add('number', TextType::class, ['required' => false,'attr' => ['class' => 'modernize', 'placeholder' => 'Numéro de téléphone']])
            ->add('opensAt', TimeType::class, ['widget' => 'single_text', 'attr' => ['class' => 'timepicker modernize', 'readonly' => true]])
            ->add('closesAt', TimeType::class, ['widget' => 'single_text', 'attr' => ['class' => 'timepicker modernize', 'readonly' => true]])
            ->add('categories', EntityType::class, ['class' => Category::class, 'multiple' => true,'attr' => ['class' => 'modernize ']])
//            ->add('city', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Ville']])
            ->add('address', AddressIncludeFormType::class, ['label' => false, 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
