<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;

abstract class Controller
{
    /**
     * Map Laravel pagination to standardized format for frontend.
     */
    protected function mapPagination(LengthAwarePaginator $paginator, ?callable $dataTransformer = null): array
    {
        return [
            'data' => $dataTransformer ? $dataTransformer($paginator->items()) : $paginator->items(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'path' => $paginator->path(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ];
    }
}
