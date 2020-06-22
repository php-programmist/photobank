<?php

namespace App\Form;

use App\Entity\Batch;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BatchType extends DependedListsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название (не обязательно)',
                'required' => false
            ])
            ->add('domain', TextType::class, [
                'label' => 'Домен (не обязательно)',
                'required' => false
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Комментарий (не обязательно)',
                'required' => false
            ])
            ->add('type', EntityType::class, [
                'label' => 'Материал',
                'required' => true,
                'class' => Type::class
            ])
            ->add('address', ChoiceType::class, [
                'label' => 'Адрес',
                'required' => false,
                'choices' => array_flip(Batch::ADDRESSES),
                'placeholder' => 'Не выбран',
            ]);
        parent::buildForm($builder, $options);
    }
}
