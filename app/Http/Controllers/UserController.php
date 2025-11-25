<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Métricas
        $totalUsuarios = User::count();
        $nuevosUsuarios = User::whereMonth('created_at', now()->month)->count();

        $query = User::query();

        // Búsqueda por nombre o email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        if ($request->filled('ordenar')) {
            switch ($request->ordenar) {
                case 'recientes': $query->latest(); break;
                case 'antiguos': $query->oldest(); break;
                case 'nombre_asc': $query->orderBy('name'); break;
                case 'nombre_desc': $query->orderByDesc('name'); break;
                default: $query->latest();
            }
        } else {
            $query->latest();
        }

        $users = $query->paginate(10)->withQueryString();
        
        return view('users.index', compact('users', 'totalUsuarios', 'nuevosUsuarios'));
    }

    /**
     * Búsqueda AJAX
     */
    public function busqueda(Request $request)
    {
        $termino = $request->input('search');

        $users = User::where(function($q) use ($termino) {
                $q->where('name', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%");
            })
            ->take(20)
            ->get();

        return response()->json($users);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Ya existe un usuario con ese email.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Ya existe un usuario con ese email.',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Show the form for changing password.
     */
    public function changePasswordForm(User $user)
    {
        return view('users.change-password', compact('user'));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Contraseña actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevenir que el usuario se elimine a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // Verificar si tiene ventas asociadas
        if ($user->ventas()->count() > 0) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene ventas asociadas.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new \App\Exports\UsersExport($request->all()), 'usuarios.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ordenar')) {
            switch ($request->ordenar) {
                case 'recientes': $query->latest(); break;
                case 'antiguos': $query->oldest(); break;
                case 'nombre_asc': $query->orderBy('name'); break;
                case 'nombre_desc': $query->orderByDesc('name'); break;
                default: $query->latest();
            }
        } else {
            $query->latest();
        }

        $users = $query->get();
        $pdf = Pdf::loadView('users.pdf', compact('users'));
        return $pdf->download('usuarios.pdf');
    }
}
