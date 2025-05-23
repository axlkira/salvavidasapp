<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index()
    {
        $profesional_documento = session('profesional_documento', '0');
        // Mostrar todos los usuarios sin filtrar por profesional_documento (solo para pruebas)
        $usuarios = DB::table('t_caracterizacion_bloque1')
            ->select('tipo_documento', 'numero_documento')
            ->get();
        return view('usuarios.index', compact('usuarios'));
    }
}
