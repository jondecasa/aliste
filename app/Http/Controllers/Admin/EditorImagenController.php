<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorImagenController extends Controller
{
    public function subir(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:4096'],
        ]);

        $ruta = $request->file('file')->store('contenido-pueblos', 'public');

        return response()->json([
            'location' => Storage::disk('public')->url($ruta),
        ]);
    }
}
