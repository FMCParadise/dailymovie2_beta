<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', null, [
            'constraints' => [
                new Length([
                    'min' => 1,
                    'max' => 100,
                    'minMessage' => 'Le nom doit comporter entre 1 et 100 caractères.',
                    'maxMessage' => 'Le nom doit comporter entre 1 et 100 caractères.',
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-ZÀ-ÿ\s\'\-]+$/',
                    'message' => 'Le nom contient des caractères spéciaux non autorisés.'
                ]),
            ],
        ])
            ->add('first_name', null, [
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 100,
                        'minMessage' => 'Le prénom doit comporter entre 1 et 100 caractères.',
                        'maxMessage' => 'Le prénom doit comporter entre 1 et 100 caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\'\-]+$/',
                        'message' => 'Le prénom contient des caractères spéciaux non autorisés.'
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'Veuillez entrer une adresse email valide.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new Length([
                        'min' => 16,
                        'minMessage' => 'La longueur du mot de passe est incorrecte. Le mot de passe doit comporter au moins 16 caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@?!"+$*#&_\-^%])[A-Za-z\d@?#"!+$*&_\-^%]{16,}$/',
                        'message' => "Le mot de passe doit contenir au moins 16 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial."
                    ])
                ],

            ])
            ->add('rgpd', CheckboxType::class, [
                'label' => 'J\'accepte la collecte et le traitement de mes données',
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],

            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

