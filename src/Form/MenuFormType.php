<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Menu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Nom du menu']])
            ->add('description', TextareaType::class, ['required' => false, 'attr' => ['class' => 'modernize', 'placeholder' => 'Description']])
            ->add('price', NumberType::class, ['attr' => ['class' => 'modernize', 'placeholder' => 'Prix']])
            ->add('category', EntityType::class, ['class' => Category::class, 'attr' => ['class' => 'modernize']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
