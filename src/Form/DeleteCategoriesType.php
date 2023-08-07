<?php

namespace App\Form;

use App\Entity\Categories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class DeleteCategoriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => "name",
                "expanded" => true,
                "multiple" => true,
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'Vous devez sélectionner au moins une catégorie.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
