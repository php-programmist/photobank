<?php

namespace App\Form;

use App\Entity\Batch;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class BatchFilterType extends DependedListsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year_month', ChoiceType::class, [
                'label' => 'Месяц создания',
                'placeholder' => 'Выберите месяц',
                'choices' => $this->getMonthChoices(),
                'mapped' => false
            ])
            ->add('using', ChoiceType::class, [
                'label' => 'Используется?',
                'choices' => [
                    'Не важно' => 0,
                    'Нет' => 1,
                    'Да' => 2,
                ],
                'expanded' => true,
                'multiple' => false,
                'mapped' => false
            ])
            ->add('type', EntityType::class, [
                'label' => 'Материал',
                'required' => false,
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

    private function getMonthChoices()
    {
        $start_year = 2019;
        $month_list = [1 => "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
        $choices = [];
        for ($year = $start_year; $year <= date('Y'); $year++) {
            foreach ($month_list as $month_number => $month_name) {
                $choices[$month_name . ' ' . $year] = $year . '-' . $month_number;
                if ($year == date('Y') && $month_number == date('m')) {
                    return $choices;
                }
            }
        }
        return $choices;
    }

    public function getBlockPrefix()
    {
        return "batch";
    }
}
