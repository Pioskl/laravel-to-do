<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Inertia\Inertia;
use phpDocumentor\Reflection\Types\Integer;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::orderBy('created_at', 'desc')->get();
        return response()->json($items);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item = Item::create($data);

        // Zwracamy pełną odpowiedź Inertia, przekierowując z powrotem do strony,
        // a nowy rekord zostaje zapisany w sesji jako flash data
        return redirect()->back()->with('newItem', $item);
    }



    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        // Pobierz zadanie lub rzuć wyjątek 404, jeśli nie istnieje
        $item = Item::findOrFail($id);

        // Walidacja danych – pole "name" jest wymagane, jeśli jest przesyłane,
        // a także akceptujemy aktualizację statusu "completed"
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'completed' => 'sometimes|boolean',
            'completed_at' => 'sometimes|nullable|date',
        ]);

        // Aktualizacja rekordu
        $item->update($validatedData);

        // Możesz zwrócić odpowiedź JSON, jeśli potrzebujesz aktualizacji po stronie frontu
        return response()->json($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $item = Item::findOrFail($id);

        if($item){
            $item->delete();
            return "Item successfully deleted";
        }
        return "Item not found";
    }
}
