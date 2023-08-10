<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;


class RegistrationFormType extends AbstractType
{

    public function __construct(private Security $security, private ParameterBagInterface $params)
    {

    }

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
                'required' => !$options['edit'],
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
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(null, "Une affiche est obligatoire"),
                    new File([
                        'maxSize' => '25M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (PNG ou JPG).',
                    ])
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $user = $event->getData();
                $form = $event->getForm();
                $isEdit = $user && $user->getId() !== null;
                $constraints = [];
                if (!$isEdit) {
                    $constraints[] = new NotBlank(null, "Une image  est obligatoire");
                }

                $constraints[] = new File([
                    'maxSize' => '25M',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                        'image/jpg',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (PNG ou JPG).',
                ]);

                $form->add('image', FileType::class, [
                    'mapped' => false,
                    'required' => !$isEdit,
                    'constraints' => $constraints,
                ]);
            });

        if (!isset($options['edit']) || !$options['edit']) {
            $builder->add('rgpd', CheckboxType::class, [
                'label' => 'J\'accepte la collecte et le traitement de mes données',
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
            ]);

        }

        $user = $this->security;
        if ($user->getUser() && in_array('ROLE_ADMIN', $user->getUser()->getRoles())) {

            $defaultRoleValue = $options["defaultRole"] ?? null ;

            $builder->add('roles', ChoiceType::class, [
                'choices' => $this->params->get('roles_app'),
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'required' => true,
                'label' => 'Rôles',
                'attr' => [
                    'class' => 'form-check-inline',
                ],
                'data' =>$defaultRoleValue
            ]);

        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'edit' => false,
            'defaultRole' => 'ROLE_AUTHOR'
        ]);
    }
}

