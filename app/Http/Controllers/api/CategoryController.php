<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Create a new category.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Update an existing category by ID.
     */
    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Delete a category by ID.
     */
    public function destroy(int $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->noContent(); // HTTP 204
    }
}