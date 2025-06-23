<?php
namespace App\Form;

use App\DTO\TaskDTO;
use App\Enum\TaskStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Tytuł',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
            ])
            ->add('deadline', DateTimeType::class, [
                'label' => 'Termin',
                'widget' => 'single_text',
            ])
            ->add('status', EnumType::class, [
                'label' => 'Status',
                'class' => TaskStatus::class,
                'choice_label' => fn(TaskStatus $status) => $status->value,
            ])
            ->add('parentTaskId', TextType::class, [
                'label' => 'ID zadania nadrzędnego',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskDTO::class,
        ]);
    }
}