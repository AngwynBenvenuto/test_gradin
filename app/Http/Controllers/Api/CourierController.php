<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $params = $request->validate([
            'search' => ['sometimes', 'string', 'max:150'],
            'level' => ['sometimes', 'string', 'regex:/^[1-5](,[1-5])*$/'],
            'sort' => ['sometimes', Rule::in(['name', 'registered_at'])],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = Courier::query();

        if (isset($params['search']) && trim($params['search']) !== '') {
            $terms = preg_split('/\s+/u', trim($params['search']));
            foreach ($terms as $term) {
                $query->where('name', 'LIKE', '%' . addcslashes($term, '%_\\') . '%');
            }
        }

        if (isset($params['level'])) {
            $query->whereIn('level', array_map('intval', explode(',', $params['level'])));
        }

        $sort = $params['sort'] ?? 'name';
        $direction = $params['direction'] ?? ($sort === 'registered_at' ? 'desc' : 'asc');

        $results = $query->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate((int) ($params['per_page'] ?? 15))
            ->withQueryString();

        return response()->json($results);
    }

    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());
        return response()->json(['message' => 'Courier created', 'data' => $courier], 201);
    }

    public function show(Courier $courier): JsonResponse
    {
        return response()->json(['data' => $courier]);
    }

    public function update(UpdateCourierRequest $request, Courier $courier): JsonResponse
    {
        $courier->update($request->validated());
        return response()->json(['message' => 'Courier updated', 'data' => $courier->refresh()]);
    }

    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();
        return response()->json(['message' => 'Courier deleted']);
    }
}
