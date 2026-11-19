<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModelShowroomRequest;
use App\Models\ModelShowroom;
use App\Services\ShowroomImage;
use Illuminate\Http\RedirectResponse;

class ModelShowroomController extends Controller
{
    public function __construct(private ShowroomImage $images) {}

    public function index()
    {
        $models = ModelShowroom::ordered()->paginate(10);

        return view('admin.models.index', compact('models'));
    }

    public function create()
    {
        return view('admin.models.create', [
            'showroom' => new ModelShowroom(['status' => true, 'display_order' => $this->nextOrder()]),
        ]);
    }

    public function store(ModelShowroomRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');
        $data['display_order'] = $data['display_order'] ?? $this->nextOrder();
        $data['image'] = $this->images->store($request->file('image'));

        ModelShowroom::create($data);

        return redirect()->route('admin.models.index')->with('success', 'Model added successfully.');
    }

    public function edit(ModelShowroom $showroom)
    {
        return view('admin.models.edit', compact('showroom'));
    }

    public function update(ModelShowroomRequest $request, ModelShowroom $showroom): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');
        $data['display_order'] = $data['display_order'] ?? $showroom->display_order;

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->replace($showroom->image, $request->file('image'));
        } else {
            unset($data['image']);
        }

        $showroom->update($data);

        return redirect()->route('admin.models.index')->with('success', 'Model updated successfully.');
    }

    public function destroy(ModelShowroom $showroom): RedirectResponse
    {
        $this->images->delete($showroom->image);
        $showroom->delete();

        return redirect()->route('admin.models.index')->with('success', 'Model deleted.');
    }

    private function nextOrder(): int
    {
        return (int) ModelShowroom::max('display_order') + 1;
    }
}
