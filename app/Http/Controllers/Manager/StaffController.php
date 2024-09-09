<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserFormRequest;
use App\Http\Services\UserService;
use App\Models\RestaurantTable;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Console\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class StaffController extends Controller
{
    public function __construct(protected UserService $userService)
    {
    }

    public function list(): Factory|View|Application
    {
        $users = User::restaurant()->whereHas('roles', function ($q) {
            $q->where('name', User::STAFF);
        })->get();

        $tables = RestaurantTable::all();

        return view('manager.staff.list', compact('users', 'tables'));
    }

    public function create(): Factory|View|Application
    {
        $tables = RestaurantTable::all();

        return view('manager.staff.create', compact('tables'));
    }

    public function store(UserFormRequest $request): RedirectResponse
    {
        $request->request->add(['role' => User::STAFF]);
        $request->request->add(['restaurant_id' => auth()->user()->restaurant_id]);

        $this->userService->store($request);

        return redirect()->route('manager.staff.list')->with(['success' => 'Staff has been saved']);
    }

    public function edit(User $user): Factory|View|Application
    {
        $tables = RestaurantTable::all();

        return view('manager.staff.edit', compact('user', 'tables'));
    }

    public function update(UserFormRequest $request, User $user): RedirectResponse
    {
        $request->request->add(['role' => User::STAFF]);

        $this->userService->update($user, $request);

        return redirect()->route('manager.staff.list')->with(['success' => 'Staff has been updated']);
    }

    public function changeStatus(Request $request, User $user): RedirectResponse
    {
        $this->userService->changeStatus($user, $request->all());

        return redirect()->route('manager.staff.list')->with(['success' => 'Employee status has been updated']);
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->userService->destroy($user);

        return redirect()->route('manager.staff.list')->with(['success' => 'Employee has been removed']);
    }

    public function dataTable(): JsonResponse
    {
        $users = User::restaurant()->whereHas('roles', function ($q) {
            $q->where('name', User::STAFF);
        })->select(['id', 'name', 'staff_type', 'status'])->get();

        return DataTables::of($users)
            ->addColumn('action', function ($staff){
                $editRoute = route('manager.staff.edit', $staff->id);
                $deleteRoute = route('manager.staff.destroy', $staff->id);

                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute'))->render();
            })
            ->editColumn('status', function ($staff){
                return "<span class='status-{$staff->status}'>".trans('labels.' .$staff->status)."</span>";
            })
            ->editColumn('staff_type', function ($staff) {
                return "<span class='status-{$staff->staff_type}'>" . trans('labels.' . $staff->staff_type) . "</span>";
            })
            ->rawColumns(['status', 'action', 'staff_type'])
            ->make();
    }
}
