<?php
/**
 * For header.html.twig
 * class  à utiliser comme service pour charger les catégorie a chaque fois que header est inclue
 */

namespace App\Twig;

use App\Repository\CategoriesRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class CategoriesForHeaderExtension extends AbstractExtension
{
    private CategoriesRepository $categoriesRepository;

    public function __construct(CategoriesRepository $categoriesRepository)
    {
        $this->categoriesRepository = $categoriesRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCategoriesForHeader', [$this, 'getCategoriesForHeader']),
        ];
    }

    public function getCategoriesForHeader(): array
    {
        return $this->categoriesRepository->findAll();
    }
}
