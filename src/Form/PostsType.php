<?php

namespace App\Form;

use App\Entity\Posts;
use App\Repository\PostsRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use App\Entity\Categories;
use App\Services\Slugger;


class PostsType extends AbstractType
{

    public function __construct(public PostsRepository $postsRepository)
    {
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $postsRepository = $this->postsRepository;

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 20,
                        'max' => 100,
                        'minMessage' => 'Le Titre doit comporter au minimum 20 caractères.',
                        'maxMessage' => 'Le Titre doit comporter au maximum 5000 caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-z0-9-_A-ZÀ-ÿ\s\'\-]+$/',
                        'message' => 'Le titre contient des caractères spéciaux non autorisés.'
                    ]),
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 20,
                        'max' => 100,
                        'minMessage' => 'Le slug doit comporter au minimum 20 caractères.',
                        'maxMessage' => 'Le slug doit comporter au maximum 100 caractères.',
                    ]),
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
            ->add('content', TextareaType::class, [
                "required" => false,
                'constraints' => [
                    new NotBlank(null, "Le contenue de  ne peut pas être vide "),
                    new Length([
                        'min' => 20,
                        'max' => 5000,
                        'minMessage' => "Le contenue de l'article doit comporter au minimum 100 caractères.",
                        'maxMessage' => "Le contenue de l'article doit comporter au maximum 5000 caractères.",
                    ]),

                ],
            ])
            ->add('categories_id', EntityType::class, [
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)  {
            $post = $event->getData();
            $form = $event->getForm();
            $isEdit = $post && $post->getId() !== null;
            $constraints = [];
            if (!$isEdit) {
                $constraints[] = new NotBlank(null, "Une affiche est obligatoire");
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

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posts::class,
        ]);
    }
}
