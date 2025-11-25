<?php

namespace App\Http\Controllers;

use App\Exports\CaducidadProductosExport;
use App\Models\Categoria;
use App\Services\CaducidadService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CaducidadController extends Controller
{
    public function __construct(
        protected CaducidadService $caducidadService,
        protected ExportService $exportService
    ) {}

    public function index(Request $request)
    {
        // Obtenemos los grupos desde el servicio (como antes)
        [$proximos, $vencidos, $revision] = $this->caducidadService->getReporteCaducidad();

        // Normalizamos a colecciones y unificamos en una sola
        $coleccion = collect($proximos)
            ->concat($vencidos)
            ->concat($revision);

        // 🔹 Filtro buscar por nombre
        if ($search = $request->input('search')) {
            $texto = mb_strtolower($search);
            $coleccion = $coleccion->filter(function ($p) use ($texto) {
                return str_contains(mb_strtolower($p->nombre ?? ''), $texto);
            });
        }

        // 🔹 Filtro por categorías (checkbox múltiple)
        if ($categorias = $request->input('categorias')) {
            $categorias = (array) $categorias;
            $coleccion = $coleccion->filter(function ($p) use ($categorias) {
                $categoriaId = $p->categoria_id ?? optional($p->categoriaRelacion)->id;
                return in_array($categoriaId, $categorias);
            });
        }

        // 🔹 Filtro por estado de caducidad (vencido, proximo, revision, ok)
        if ($estado = $request->input('estado')) {
            $coleccion = $coleccion->filter(function ($p) use ($estado) {
                // estado_vencimiento viene del servicio/modelo: vencido, critico, proximo, revisar, ok
                return ($p->estado_vencimiento ?? null) === $estado;
            });
        }

        // 🔹 Ordenar por fecha de vencimiento (ascendente)
        $coleccion = $coleccion
            ->sortBy(function ($p) {
                return optional($p->fecha_vencimiento)->timestamp ?? PHP_INT_MAX;
            })
            ->values();

        // 🔹 verTodo / paginación (mismo patrón que productos/ventas)
        $verTodo   = $request->boolean('verTodo');
        $porPagina = $verTodo ? $coleccion->count() : 10;
        $porPagina = $porPagina > 0 ? $porPagina : 10;

        $page   = LengthAwarePaginator::resolveCurrentPage();
        $total  = $coleccion->count();
        $items  = $coleccion->forPage($page, $porPagina)->values();

        $productos = new LengthAwarePaginator(
            $items,
            $total,
            $porPagina,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Categorías para el filtro
        $categorias = Categoria::all();

        return view('caducidad.index', compact(
            'productos',
            'categorias',
            'verTodo',
            'proximos',
            'vencidos',
            'revision'
        ));
    }

    public function exportPdf()
    {
        [$proximos, $vencidos, $revision] = $this->caducidadService->getReporteCaducidad();

        return $this->exportService->downloadPdf(
            'caducidad.pdf',
            compact('proximos', 'vencidos', 'revision'),
            'reporte_caducidad_productos.pdf'
        );
    }

    public function exportExcel()
    {
        [$proximos, $vencidos, $revision] = $this->caducidadService->getReporteCaducidad();

        return $this->exportService->downloadExcel(
            new CaducidadProductosExport($proximos, $vencidos, $revision),
            'caducidad_productos.xlsx'
        );
    }
}