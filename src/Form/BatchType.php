<?php

namespace App\Form;

use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BatchType extends DependedListsType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('domain',TextType::class,['label'=>'Домен (не обязательно)','required'=>false]);
        $builder->add('comment',TextareaType::class,['label'=>'Комментарий (не обязательно)','required'=>false]);
        $builder->add('type',EntityType::class,['label'=>'Материал','required'=>true,'class'=>Type::class]);
        parent::buildForm($builder,$options);
    }
}
