<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour la création ou la suppression d’un cours.
 * Permet de saisir les informations de base telles que :
 * le titre, la description, la catégorie, le prix et une image.
 */
class CourseType extends AbstractType
{
    /**
     * Construit les champs du formulaire de cours.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire Symfony
     * @param array $options Options passées au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour le titre du cours
            ->add('title', TextType::class, [
                'label' => 'Titre du cours'
            ])

            // Champ pour la description courte du cours
            ->add('description', TextType::class, [
                'label' => 'Description'
            ])

            // Champ pour la catégorie (ex: Musique, Informatique...)
            ->add('category', TextType::class, [
                'label' => 'Catégorie',
                'attr' => [
                    'placeholder' => 'Ex: Informatique, Musique...'
                ]
            ])

            // Champ pour le prix du cours en euros
            ->add('price', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR'
            ])

            // Champ pour l’upload d’une image, non lié directement à l'entité
            ->add('imageFile', FileType::class, [
                'label' => 'Image du cours',
                'mapped' => false, // Ne correspond pas directement à une propriété de l'entité
                'required' => false, // Le champ est facultatif
            ]);
    }

    /**
     * Configure les options du formulaire, notamment l'entité liée.
     *
     * @param OptionsResolver $resolver Le résolveur d’options de formulaire
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class, // Entité liée à ce formulaire
        ]);
    }
}
