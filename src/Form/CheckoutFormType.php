<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\PaymentMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckoutFormType extends AbstractType
{
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
                'choices' => $this->user->getAddresses()
            ])
            ->add('paymentMethod', EntityType::class, [
                'class' => PaymentMethod::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
                'label' => false
            ])
            ->add('creditcard', PaymentFormType::class, ['label' => false])
            ->add('pay', SubmitType::class, [
                'attr' => [
                    'class' => 'stripe-btn'
                ],
                'label' => 'Commander'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
