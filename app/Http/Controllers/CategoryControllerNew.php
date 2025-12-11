<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryControllerNew extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        try {
            $response = $this->api->get('categories');
            $categoriesData = $response['data'] ?? [];
            
            // Convert to objects
            $categoriesData = array_map(fn($cat) => (object) $cat, $categoriesData);
            
            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $total = count($categoriesData);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedData = array_slice($categoriesData, $offset, $perPage);

            $categories = new LengthAwarePaginator(
                $paginatedData,
                $total,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('admin.categories.index', compact('categories'));

        } catch (\Exception $e) {
            Log::error('Categories index error: ' . $e->getMessage());
            $categories = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);
            return view('admin.categories.index', ['categories' => $categories, 'error' => $e->getMessage()]);
        }
    }
}