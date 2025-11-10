<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alias;
use App\Http\Requests\UpdateAliasRequest;
use App\Http\Requests\StoreAliasRequest;
use Illuminate\Http\RedirectResponse;

class AliasController extends Controller
{

    public function store(StoreAliasRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $alias = Alias::create([
            'user_id' => $validated['user_id'],
            'group_user_id' => $validated['group_user_id'],
            'alias' => $validated['alias'],
        ]);
             
        return redirect()->route('group.index')->with('status', 'Alias created successfully.');
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAliasRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Alias::where('id', $validated['id'])->update(['alias' => $validated['alias']]);

        return redirect()->route('group.index')->with('status', 'Alias updated successfully.');
    }
}
