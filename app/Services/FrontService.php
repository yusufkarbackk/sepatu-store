<?php

namespace App\Services;

use App\Models\Shoe;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ShoeRepositoryInterface;

class FrontService
{
    protected $categoryRepository;
    protected $shoeRepository;

    public function __construct(ShoeRepositoryInterface $shoeRepository, CategoryRepositoryInterface $categoryRepository)
    {
        $this->shoeRepository = $shoeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function searchShoes(string $keyword)
    {
        return $this->shoeRepository->searchByName($keyword);
    }

    public function getFrontPageData()
    {
        $popularShoes = $this->shoeRepository->getPopularShoes(4);
        $newShoes = $this->shoeRepository->getAllNewShoes();
        $categories = $this->categoryRepository->getAllCategories();
    }
}
