<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableFormRequest;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Http\Services\RestaurantTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;


class RestaurantTableController extends Controller
{
    public function __construct(protected RestaurantTableService $tableService)
    {
    }

    public function list(): Factory|View|Application
    {
        return view('manager.tables.list');
    }

    public function create(): Factory|View|Application
    {
        $statuses = RestaurantTable::STATUSES;

        $latestTable = RestaurantTable::latest('id')->first();

        return view('manager.tables.create', compact('statuses', 'latestTable'));
    }

    public function store(TableFormRequest $request): RedirectResponse
    {
        $this->tableService->store($request);

        return redirect()->route('manager.table.list')->with(['success' => 'Table has been saved']);
    }

    public function edit(RestaurantTable $table): Factory|View|Application
    {
        return view('manager.tables.edit', compact('table'));
    }

    public function update(TableFormRequest $request, RestaurantTable $table): RedirectResponse
    {
        $response = $this->tableService->update($table, $request);

        if(is_int($response) && $response > 0) {
            return redirect()->route('manager.table.list')->with(['error' => 'Table has been updated. WARNING: There '.trans_choice('labels.be', $response).' currently '.number_format($response).' '.trans_choice('labels.order', $response).' for that table.']);
        }

        return redirect()->route('manager.table.list')->with(['success' => 'Table has been updated.']);
    }

    public function regenerateQr(RestaurantTable $table): RedirectResponse
    {
        $this->tableService->regenerateQr($table);

        return redirect()->route('manager.table.edit', $table->id)->with(['success' => 'Qr has been generated']);
    }

    public function exportZip()
    {
        $fileurl = $this->tableService->createZip();

        if (!$fileurl){
            return redirect()->route('manager.table.list')->with(['error' => 'No tables!']);
        }

        return response()->download($fileurl, 'Codes.zip', array('Content-Type: application/octet-stream','Content-Length: '. filesize($fileurl)))->deleteFileAfterSend(true);
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        return redirect()->route('manager.table.list')->with($this->tableService->destroy($table) ? ['success' => __('labels.table-deleted')] : ['error' => __('labels.table-not-deleted')]);
    }

    public function dataTable(): JsonResponse
    {
        $tables = RestaurantTable::select(['id', 'name', 'type', 'status'])->get();

        return DataTables::of($tables)
            ->editColumn('name', function ($table){
                return $table->name;
            })
            ->editColumn('room', function ($table){
                return "<span class='type-{$table->room}'>".$table->room."</span>";
            })
            ->editColumn('type', function ($table){
                return "<span class='type-{$table->type}'>".trans('labels.' .$table->type)."</span>";
            })
            ->editColumn('status', function ($table){
                return "<span class='status-{$table->status}'>".trans('labels.' .$table->status)."</span>";
            })
            ->addColumn('action', function ($table){
                $editRoute = route('manager.table.edit', $table->id);
                $deleteRoute = route('manager.table.destroy', $table->id);

                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute'))->render();
            })

            ->rawColumns(['status', 'action', 'type','room'])
            ->make();
    }
}
