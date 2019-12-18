<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BatchType extends DependedListsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('domain',TextType::class,['label'=>'Домен (не обязательно)','required'=>false]);
        $builder->add('comment',TextareaType::class,['label'=>'Комментарий (не обязательно)','required'=>false]);
        parent::buildForm($builder,$options);
    }
}
