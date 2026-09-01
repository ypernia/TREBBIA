<?php

namespace App\Http\Controllers;

class ModuleController extends Controller
{
    public function __invoke(string $module)
    {
        $modules = collect(config('trebbia.modules'));
        abort_unless($modules->has($module), 404);

        return view('modules.show', [
            'module' => $modules->get($module),
            'moduleKey' => $module,
            'business' => app('activeBusiness'),
        ]);
    }
}
