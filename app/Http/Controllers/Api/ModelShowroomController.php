<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModelShowroomRequest;
use App\Http\Resources\ModelShowroomResource;
use App\Models\ModelShowroom;
use App\Services\ShowroomImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ModelShowroomController extends Controller
{
    public function __construct(private ShowroomImage $images) {}

    public function index(): AnonymousResourceCollection
    {
        return ModelShowroomResource::collection(ModelShowroom::active()->ordered()->get());
    }

    public function show(int $id): ModelShowroomResource
    {
        return new ModelShowroomResource(ModelShowroom::active()->findOrFail($id));
    }

    public function store(ModelShowroomRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);
        $data['display_order'] = $data['display_order'] ?? ((int) ModelShowroom::max('display_order') + 1);
        $data['image'] = $this->images->store($request->file('image'));

        $model = ModelShowroom::create($data);

        return (new ModelShowroomResource($model))->response()->setStatusCode(201);
    }

    public function update(ModelShowroomRequest $request, int $id): ModelShowroomResource
    {
        $model = ModelShowroom::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->replace($model->image, $request->file('image'));
        } else {
            unset($data['image']);
        }

        if (array_key_exists('display_order', $data) && $data['display_order'] === null) {
            unset($data['display_order']);
        }

        $model->update($data);

        return new ModelShowroomResource($model->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $model = ModelShowroom::findOrFail($id);
        $this->images->delete($model->image);
        $model->delete();

        return response()->json(['message' => 'Model deleted.']);
    }
}
