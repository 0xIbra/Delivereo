<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('usernameCanonical')
            ->add('email')
            ->add('emailCanonical')
            ->add('enabled')
//            ->add('salt')
            ->add('password')
//            ->add('lastLogin')
            ->add('confirmationToken')
//            ->add('passwordRequestedAt')
//            ->add('roles')
            ->add('firstName')
            ->add('lastName')
            ->add('createdAt')
            ->add('image')
            ->add('gender')
//            ->add('addresses')
//            ->add('restaurant')
//            ->add('managedRestaurant')
//            ->add('cart')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
