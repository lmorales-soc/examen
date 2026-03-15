<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Area\CreateAreaUseCase;
use App\Application\UseCases\Area\DeleteAreaUseCase;
use App\Application\UseCases\Area\GetAreaUseCase;
use App\Application\UseCases\Area\ListAreasUseCase;
use App\Application\UseCases\Area\UpdateAreaUseCase;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function __construct(
        private readonly ListAreasUseCase $listAreasUseCase,
        private readonly GetAreaUseCase $getAreaUseCase,
        private readonly CreateAreaUseCase $createAreaUseCase,
        private readonly UpdateAreaUseCase $updateAreaUseCase,
        private readonly DeleteAreaUseCase $deleteAreaUseCase,
    ) {
    }

    public function index(Request $request): View
    {
        $activeOnly = $request->boolean('active_only', true);
        $areas = $this->listAreasUseCase->execute($activeOnly);
        $managerIds = array_filter(array_unique(array_column($areas, 'manager_user_id')));
        $managerNames = $managerIds ? User::whereIn('id', $managerIds)->pluck('name', 'id')->all() : [];

        return view('areas.index', [
            'areas' => $areas,
            'managerNames' => $managerNames,
        ]);
    }

    public function create(): View
    {
        return view('areas.create');
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        $this->createAreaUseCase->execute($request->validated());

        return redirect()->route('areas.index')->with('success', 'Área creada correctamente.');
    }

    public function show(int $area): View|RedirectResponse
    {
        $areaData = $this->getAreaUseCase->execute($area);
        if (! $areaData) {
            return redirect()->route('areas.index')->with('error', 'Área no encontrada.');
        }
        $areaData['manager_name'] = $areaData['manager_user_id']
            ? (User::find($areaData['manager_user_id'])?->name ?? '—')
            : null;

        return view('areas.show', [
            'area' => $areaData,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(int $area): View|RedirectResponse
    {
        $areaData = $this->getAreaUseCase->execute($area);
        if (! $areaData) {
            return redirect()->route('areas.index')->with('error', 'Área no encontrada.');
        }

        return view('areas.edit', [
            'area' => $areaData,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateAreaRequest $request, int $area): RedirectResponse
    {
        $data = $request->validated();
        if (array_key_exists('manager_user_id', $data) && $data['manager_user_id'] === '') {
            $data['manager_user_id'] = null;
        }
        $this->updateAreaUseCase->execute($area, $data);

        return redirect()->route('areas.index')->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(int $area): RedirectResponse
    {
        try {
            $this->deleteAreaUseCase->execute($area);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('areas.index')->with('error', $e->getMessage());
        }

        return redirect()->route('areas.index')->with('success', 'Área eliminada correctamente.');
    }
}
