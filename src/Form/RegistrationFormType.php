<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire d'inscription d'un nouvel utilisateur.
 * Permet de renseigner l'email, le nom d'utilisateur, l'adresse et le mot de passe.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Construit les champs du formulaire d'inscription.
     *
     * @param FormBuilderInterface $builder Générateur de formulaire
     * @param array $options Options éventuelles
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour l'adresse email
            ->add('email', EmailType::class, [
                'label' => 'Email :',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])

            // Champ pour le nom d'utilisateur
            ->add('username', TextType::class, [
                'label' => 'Nom utilisateur :',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])

            // Champ optionnel pour l'adresse de livraison
            ->add('address', TextType::class, [
                'label' => 'Adresse de livraison :',
                'attr' => ['class' => 'form-control'],
                'required' => false, 
            ])

            // Champ pour le mot de passe avec contraintes de sécurité
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe :',
                'mapped' => false, // Ce champ ne correspond pas directement à une propriété de l'entité User
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    // Le mot de passe ne doit pas être vide
                    new Assert\NotBlank([
                        'message' => 'Le mot de passe est obligatoire.',
                    ]),
                    // Longueur minimale de 8 caractères
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.',
                    ]),
                    // Doit contenir au moins une majuscule
                    new Assert\Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule.',
                    ]),
                    // Doit contenir au moins une minuscule
                    new Assert\Regex([
                        'pattern' => '/[a-z]/',
                        'message' => 'Le mot de passe doit contenir au moins une minuscule.',
                    ]),
                    // Doit contenir au moins un chiffre
                    new Assert\Regex([
                        'pattern' => '/\d/',
                        'message' => 'Le mot de passe doit contenir au moins un chiffre.',
                    ]),
                    // Doit contenir au moins un caractère spécial
                    new Assert\Regex([
                        'pattern' => '/[@$!%*?&]/',
                        'message' => 'Le mot de passe doit contenir au moins un caractère spécial (@$!%*?&).',
                    ]),
                ],
            ]);
    }

    /**
     * Configure les options par défaut de ce formulaire.
     * Ici, on associe le formulaire à l'entité User.
     *
     * @param OptionsResolver $resolver Résolveur d’options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
