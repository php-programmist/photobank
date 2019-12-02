<?php

namespace App\Form;

use App\Entity\Batch;
use App\Entity\Brand;
use App\Entity\Model;
use App\Entity\Service;
use App\Entity\ServiceCategory;
use App\Repository\ModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DependedListsType extends AbstractType
{
    protected $em;
    
    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Batch::class,
        ]);
    }
    
    protected function addElements(FormInterface $form, Brand $brand = null, ServiceCategory $category = null)
    {
        $form->add('brand', EntityType::class, [
            'required'     => true,
            'data'         => $brand,
            'placeholder'  => 'Выберите марку',
            'choice_label' => 'name',
            'class'        => Brand::class,
            'label'        => 'Марка',
        ]);
        
        $models = [];
        
        if ($brand) {
            
            $model_repository = $this->em->getRepository(Model::class);
            
            $models = $model_repository->createQueryBuilder("q")
                                       ->where("q.brand = :brandid")
                                       ->setParameter("brandid", $brand->getId())
                                       ->getQuery()
                                       ->getResult();
        }
        
        $form->add('model', EntityType::class, array(
            'required'     => true,
            'placeholder'  => 'Сначала выберите марку...',
            'class'        => Model::class,
            'choices'      => $models,
            'choice_label' => 'name',
            'label'        => 'Модель',
        ));
        
        $form->add('serviceCategory', EntityType::class, [
            'required'     => true,
            'data'         => $category,
            'placeholder'  => 'Выберите категорию',
            'choice_label' => 'name',
            'class'        => ServiceCategory::class,
            'label'        => 'Категория',
        ]);
        
        $services = [];
        
        if ($category) {
            
            $service_repository = $this->em->getRepository(Service::class);
            
            $services = $service_repository->createQueryBuilder("q")
                                           ->where("q.service_category = :category_id")
                                           ->setParameter("category_id", $category->getId())
                                           ->getQuery()
                                           ->getResult();
        }
        
        $form->add('service', EntityType::class, array(
            'required'     => true,
            'placeholder'  => 'Сначала выберите категорию...',
            'class'        => Service::class,
            'choices'      => $services,
            'choice_label' => 'name',
            'label'        => 'Услуга',
        ));
        
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        
        $brand    = $this->em->getRepository(Brand::class)->find($data['brand']);
        $category = $this->em->getRepository(ServiceCategory::class)->find($data['serviceCategory']);
        
        $this->addElements($form, $brand, $category);
    }
    
    public function onPreSetData(FormEvent $event)
    {
        $batch = $event->getData();
        $form  = $event->getForm();
        
        $brand    = $batch->getBrand() ? $batch->getBrand() : null;
        $category = $batch->getServiceCategory() ? $batch->getServiceCategory() : null;
        
        $this->addElements($form, $brand, $category);
    }
}
