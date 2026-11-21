<?php

namespace App\Http\Controllers;

use App\Models\BahanPembersih;
use App\Models\Supplier;
use Illuminate\Http\Request;

class BahanController extends Controller
{
    public function index()
    {
        $bahan = BahanPembersih::with('supplier')->get();
        return view('bahan.index', compact('bahan'));
    }

    public function create()
    {
        $supplier = Supplier::all();
        return view('bahan.tambah', compact('supplier'));
    }

    public function store(Request $request)
    {
        BahanPembersih::create($request->all());
        return redirect()->route('bahan.index');
    }

    public function edit($id)
    {
        $bahan = BahanPembersih::findOrFail($id);
        $supplier = Supplier::all();
        return view('bahan.edit', compact('bahan', 'supplier'));
    }

    public function update(Request $request, $id)
    {
        $bahan = BahanPembersih::findOrFail($id);
        $bahan->update($request->all());
        return redirect()->route('bahan.index');
    }

    public function destroy($id)
    {
        $user = auth('admin')->user();
        if (($user->peran ?? $user->role ?? '') !== 'admin') {
            abort(403, 'Hanya admin yang dapat menghapus data bahan.');
        }

        $bahan = BahanPembersih::findOrFail($id);
        $bahan->delete();
        return redirect()->route('bahan.index');
    }
}