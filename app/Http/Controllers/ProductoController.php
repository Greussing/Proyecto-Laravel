<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Protege todas las rutas
    }

    // Mostrar todos los productos
    public function index()
    {
        $productos = Producto::paginate(10);
        return view('productos.index', compact('productos'));
    }

    // Mostrar formulario para crear producto
    public function create()
    {
        return view('productos.create');
    }

    // Guardar producto nuevo
    public function store(Request $request)
    {
        $request->validate([
    'nombre' => 'required|string|max:255',
    'cantidad' => 'required|integer|min:0',
    'precio' => 'required|numeric|min:0',
    'categoria' => 'nullable|string|max:255',
]);

        Producto::create($request->all());

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente');
    }

    public function show(Producto $producto)
{
    return view('productos.show', compact('producto'));
}

    // Mostrar formulario para editar producto existente
    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    // Actualizar producto en BD
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
    'nombre' => 'required|string|max:255',
    'cantidad' => 'required|integer|min:0',
    'precio' => 'required|numeric|min:0',
    'categoria' => 'nullable|string|max:255',
]);

        $producto->update($request->all());

        return redirect()->route('productos.index')->with('success', 'Producto actualizado');
    }

    // Eliminar producto
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')->with('success', 'Producto eliminado');
    }
}