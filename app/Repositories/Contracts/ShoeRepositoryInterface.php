<?php

namespace App\Repositories\Contracts;

interface ShoeRepositoryInterface
{
    public function getPopularShoes($limit);
    public function getAllNewShoes();
    public function find(array $data);
    public function getPrice($id);
    public function searchByName(string $keyword);
}