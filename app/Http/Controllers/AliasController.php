<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alias;
use App\Http\Requests\UpdateAliasRequest;
use Illuminate\Http\RedirectResponse;

class AliasController extends Controller
{
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
