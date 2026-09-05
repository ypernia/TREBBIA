<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBusinessController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        return view('admin.businesses.index', [
            'search' => $search,
            'businesses' => Business::query()
                ->with(['owner', 'subscription.plan'])
                ->withCount(['clients', 'appointments', 'professionals'])
                ->when($search, fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('owner', fn (Builder $query): Builder => $query->where('email', 'like', "%{$search}%"));
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
}
