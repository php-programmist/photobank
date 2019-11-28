<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Yandex\OAuth\OAuthClient;

/**
 * @Route("/yandex", name="yandex_")
 */
class YandexController extends AbstractController
{
    
    private $client_id;
    private $secret_key;
    
    public function __construct(ParameterBagInterface $params)
    {
        $yandex           = $params->get('yandex');
        $this->client_id  = $yandex['client_id'];
        $this->secret_key = $yandex['secret_key'];
    }
    
    /**
     * @Route("/auth", name="auth")
     */
    public function authRedirect()
    {
        $client = new OAuthClient($this->client_id);
        $client->authRedirect(true);
        $state = 'yandex-php-library';
        $client->authRedirect(true, OAuthClient::CODE_AUTH_TYPE, $state);
    }
    
    /**
     * @Route("/auth-callback", name="auth_callback")
     */
    public function authCallback()
    {
        $client = new OAuthClient($this->client_id, $this->secret_key);
        try{
            // осуществляем обмен
            $client->requestAccessToken($_REQUEST['code']);
        } catch (\Exception $e){
            $this->addFlash('warning',$e->getMessage());
        }
        $token = $client->getAccessToken();
        /** @var User $user*/
        $user = $this->getUser();
        $user->setYandexToken($token);
        $date = new \DateTimeImmutable();
        $date = $date->add(new \DateInterval('P1Y'));
        $user->setYandexTokenExpire($date);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        $this->addFlash('success',"Вы успешно прошли авторизацию на Yandex.Disk");
        return $this->redirectToRoute('batch_index');
    }
}
