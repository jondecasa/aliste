<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\OptimizadorImagenes;
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

        $ruta = OptimizadorImagenes::guardar($request->file('file'), 'contenido-pueblos');

        return response()->json([
            'location' => Storage::disk('public')->url($ruta),
        ]);
    }
}
