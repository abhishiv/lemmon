<?php

namespace App\Http\Controllers\Manager;


use App\Models\Extra;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Services\ExtraService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExtraFormRequest;
use App\Http\Requests\ImageFormRequest;
use Yajra\DataTables\Facades\DataTables;

class ExtraController extends Controller
{
    public function __construct(protected ExtraService $extraService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $extras = Extra::where('restaurant_id', auth()->user()->restaurant_id)->get();

        return view('manager.extras.list', compact('extras'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $statuses = Extra::STATUSES;

        return view('manager.extras.create', compact('statuses'));
    }
    
    public function dataTable(): JsonResponse
    {
        $extras = Extra::select(['id', 'title', 'description', 'status'])->get();

        return DataTables::of($extras)
            ->addColumn('action', function ($extra){
                $editRoute = route('manager.extra.edit', $extra->id);
                $deleteRoute = route('manager.extra.destroy', $extra->id);

                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute'))->render();
            })
            ->addColumn('title', function ($extra){
                return Str::limit($extra->title, 50, '');
            })
            ->editColumn('description', function ($extra){
                return Str::limit($extra->description, 50, '');
            })
            ->editColumn('status', function ($extra){
                return "<span class='status-{$extra->status}'>" . __('labels.' .$extra->status) . "</span>";
            })
            ->removeColumn('id')
            ->removeColumn('images')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ExtraFormRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExtraFormRequest $request)
    {
        $this->extraService->store($request);

        return redirect()->route('manager.extra.list')->with('success', 'Extra created successfully');
    }
    
    public function verifyImage(ImageFormRequest $request): JsonResponse
    {
        return response()->json(['success' => 'ok'], 200);
    }

    public function getImages(Extra $extra): bool|string
    {
        $images = $extra->getImages();

        return json_encode($images);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function show(Extra $extra)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function edit(Extra $extra)
    {
        $statuses = Extra::STATUSES;

        return view('manager.extras.edit', compact('extra', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateExtraRequest  $request
     * @param  \App\Models\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function update(ExtraFormRequest $request, Extra $extra)
    {
        $this->extraService->update($extra, $request);

        return redirect()->route('manager.extra.list')->with('success', 'Extra updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function destroy(Extra $extra)
    {
        $this->extraService->destroy($extra);

        return redirect()->route('manager.extra.list')->with('success', 'Extra deleted successfully');
    }
}
