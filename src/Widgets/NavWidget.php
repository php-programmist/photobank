<?php

namespace App\Widgets;

use App\Entity\User;
use App\Services\YandexDiskService;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavWidget extends AbstractExtension
{
    /**
     * @var YandexDiskService
     */
    protected $disk_service;
    /**@var User*/
    private $user;
    
    public function __construct(Security $security,YandexDiskService $disk_service)
    {
        $this->user = $security->getUser();
        $this->disk_service = $disk_service;
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('nav', [$this, 'render'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }
    
    public function render(Environment $twig): string
    {
        if (! $this->user) {
            return '';
        }
        
        return $twig->render('widgets/nav.html.twig', [
            'email'     => $this->user->getUsername(),
            'yandex_username' => $this->disk_service->getLogin(),
            'yandex_free_space'=> $this->disk_service->getFreeSpace()
        ]);
    }
}