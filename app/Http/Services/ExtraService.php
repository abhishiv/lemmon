<?php

namespace App\Http\Services;

use App\Http\Requests\ExtraFormRequest;
use App\Models\Extra;
use App\Traits\ImageTrait;

class ExtraService
{
    use ImageTrait;

    public function store(ExtraFormRequest $request): Extra
    {
        $extra = Extra::create([
            'title' => $request->title,
            'description' => $request->description,
            'restaurant_id' => auth()->user()->restaurant_id,
        ]);

        $this->upload($extra, $request['images']);

        return $extra;
       
    }

    public function update(Extra $extra, $request): Extra
    {
        $validated = $request->validated();

        $extra->update($validated);

        $this->upload($extra, $request['images']);

        return $extra;
    }

    public function destroy(Extra $extra): void
    {
        $extra->deleted_at = now();
        $extra->save();
    }
}
