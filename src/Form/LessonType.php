<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Entity\Course;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre de la leçon'])
            ->add('content', TextType::class, ['label' => 'Contenu'])
            ->add('price', MoneyType::class, ['label' => 'Prix (€)', 'currency' => 'EUR'])
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
                'label' => 'Cours associé'
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}
