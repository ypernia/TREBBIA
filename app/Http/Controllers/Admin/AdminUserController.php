<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        return view('admin.users.index', [
            'search' => $search,
            'users' => User::query()
                ->withCount('businesses')
                ->when($search, fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
}
