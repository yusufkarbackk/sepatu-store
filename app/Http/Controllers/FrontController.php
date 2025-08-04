<?php

namespace App\Http\Controllers;

use App\Models\Shoe;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    protected $frontService;

    public function __construct(FrontService $frontService)
    {
        $this->frontService = $frontService;
    }

    public function index()
    {
        $shoes = $this->frontService->getFrontPageData();
        return view('front.index', $shoes);
    }

    public function details(Shoe $shoe)
    {
        return view('front.details', compact('shoe'));
    }

    public function category($category)
    {
        $shoes = $this->frontService->getCategoryData($category);
        return view('front.category', compact('category'));
    }
}
