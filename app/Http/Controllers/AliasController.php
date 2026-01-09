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
    public function update(UpdateAliasRequest $request, Alias $alias): RedirectResponse
    {   
        dump($request->all());
        if ($request->user()->cannot('update', $alias)) {
            return redirect()->route('group.index')->withErrors(['id' => 'You do not have permission to update this alias.']);
           
        } 
        $validated = $request->validated();

        $alias->update(['alias' => $validated['alias']]);

        return redirect()->route('group.index')->with('status', 'Alias updated successfully.');
    }
}
