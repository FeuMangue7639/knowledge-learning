<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Entity\Course;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire utilisé pour créer ou supprimer une leçon.
 * Il permet de renseigner le titre, le contenu, le prix, 
 * ainsi que d’associer la leçon à un cours existant.
 */
class LessonType extends AbstractType
{
    /**
     * Construit les champs du formulaire de leçon.
     *
     * @param FormBuilderInterface $builder Le générateur de formulaire
     * @param array $options Les options passées au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour le titre de la leçon
            ->add('title', TextType::class, [
                'label' => 'Titre de la leçon'
            ])

            // Champ pour le contenu de la leçon (texte court ou long)
            ->add('content', TextType::class, [
                'label' => 'Contenu'
            ])

            // Champ pour le prix de la leçon en euros
            ->add('price', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR'
            ])

            // Champ pour sélectionner le cours auquel cette leçon appartient
            ->add('course', EntityType::class, [
                'class' => Course::class,             // Classe de l'entité liée
                'choice_label' => 'title',            // Ce qui est affiché dans le menu déroulant
                'label' => 'Cours associé'            // Libellé affiché pour l'utilisateur
            ]);
    }

    /**
     * Configure les options de ce formulaire.
     * Ici on associe ce formulaire à l'entité Lesson.
     *
     * @param OptionsResolver $resolver Résolveur d'options Symfony
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}
