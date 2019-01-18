<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', TelType::class, ['label' => false, 'attr' => ['class' => 'modernize card-number', 'placeholder' => '1234 4567 8910 1112', 'maxlength' => 19], 'required' => false])
            ->add('expiration', TelType::class, ['label' => false, 'attr' => ['class' => 'modernize expiration', 'placeholder' => '01/19', 'maxlength' => 5], 'required' => false])
            ->add('cvc', TelType::class, ['label' => false, 'attr' => ['class' => 'modernize cvc', 'placeholder' => '123', 'maxlength' => 3], 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
