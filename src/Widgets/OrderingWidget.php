<?php

namespace App\Widgets;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OrderingWidget extends AbstractExtension
{
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ordering', [$this, 'render'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }
    
    public function render(Environment $twig, $field, $label): string
    {
        return $twig->render('widgets/ordering.html.twig', compact('field','label'));
    }
}