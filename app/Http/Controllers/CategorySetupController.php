<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategorySetupController extends Controller
{
    public function edit(Request $request): View
    {
        return view('category-setup', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'categoria_principal' => ['required', 'in:Naturaleza,Linea blanca,Tecnologia,Cocina,Educacion,Otro'],
        ]);

        $request->user()->update($data);

        return to_route('dashboard');
    }
}