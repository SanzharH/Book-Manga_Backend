<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        // Для каждой категории — форматы опубликованных товаров (book, manga),
        // чтобы фронтенд показывал в фильтре только жанры выбранного типа
        $formats = Product::query()
            ->where('is_published', true)
            ->select('category_id', 'format')
            ->distinct()
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rows) => $rows->pluck('format')->sort()->values()->all());

        $categories = Category::query()
            ->orderBy('name')
            ->get()
            ->each(fn (Category $category) => $category->setAttribute(
                'formats',
                $formats->get($category->id, []),
            ));

        return CategoryResource::collection($categories);
    }
}
