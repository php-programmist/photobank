<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends EasyAdminController
{
    /**
     * @var AdapterInterface
     */
    protected $cache;
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $password_encoder;
    
    public function __construct(AdapterInterface $cache, UserPasswordEncoderInterface $password_encoder)
    {
        $this->cache            = $cache;
        $this->password_encoder = $password_encoder;
    }
    
    /**
     * @Route("/cache-clear", name="admin_cache_clear")
     */
    public function cacheClearAction()
    {
        if ($this->cache->clear()) {
            return $this->json(['status' => true, 'msg' => 'Кэш очищен']);
        } else {
            return $this->json(['status' => false, 'msg' => 'Произошла ошибка']);
        }
    }
    
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToReferrer()
    {
        $refererAction = $this->request->query->get('action');
        // from new|edit action, redirect to edit if possible
        if (in_array($refererAction, array(
                'new',
                'edit',
            )) && $this->request->request->get('referer') === 'apply' && $this->isActionAllowed('edit')) {
            return $this->redirectToRoute('easyadmin', array(
                'action'       => 'edit',
                'entity'       => $this->entity['name'],
                'menuIndex'    => $this->request->query->get('menuIndex'),
                'submenuIndex' => $this->request->query->get('submenuIndex'),
                'id'           => ('new' === $refererAction)
                    ? PropertyAccess::createPropertyAccessor()->getValue($this->request->attributes->get('easyadmin')['item'],
                        $this->entity['primary_key_field_name'])
                    : $this->request->query->get('id'),
            ));
        }
        
        return parent::redirectToReferrer();
    }
    
    protected function updateUserEntity(User $entity){
        $this->setPassword($entity);
        $this->updateEntity($entity);
    }
    
    protected function persistUserEntity(User $entity){
        $this->setPassword($entity);
        $this->persistEntity($entity);
    }
    
    private function setPassword($entity)
    {
        if ($entity instanceof User && $entity->getPlainPassword()) {
            $entity->setPassword($this->password_encoder->encodePassword($entity,$entity->getPlainPassword()));
        }
    }
}