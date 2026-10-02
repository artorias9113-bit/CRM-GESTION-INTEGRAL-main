<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * REPORTE 1 — Clientes por Zona Geográfica
     *
     * Consulta la tabla 'clients', agrupa por zona_geografica,
     * cuenta cuántos clientes hay en cada zona y calcula el porcentaje.
     * Incluye el RETO: solo muestra zonas con más del 15% del total.
     */
    public function clientesPorZona()
    {
        // 1. Consulta con Query Builder
        // Traemos la zona y contamos cuántos clientes hay en cada una
        $zonas = DB::table('clients')                       // HUECO A: tabla 'clients'
            ->select(
                'zona_geografica',                          // HUECO B: columna a mostrar
                DB::raw('COUNT(*) as total')                // HUECO C: función de agregación
            )
            ->groupBy('zona_geografica')                    // HUECO D: agrupar por zona
            ->orderByDesc('total')                          // HUECO E: ordenar de mayor a menor
            ->get();                                        // HUECO F: ejecutar y obtener colección

        // 2. Total general (suma de todos los clientes)
        $totalGeneral = $zonas->sum('total');               // HUECO G: método sum() de Collection

        // 3. Agregar porcentaje a cada zona usando map()
        $zonasConPorcentaje = $zonas->map(function ($zona) use ($totalGeneral) {  // HUECO H: map()
            $zona->porcentaje = $totalGeneral > 0
                ? round(($zona->total / $totalGeneral) * 100, 2)  // HUECO I: $zona->total
                : 0;
            return $zona;                                           // HUECO J: retornar el objeto modificado
        });

        // RETO DE MODIFICACIÓN: solo mostrar zonas con más del 15% del total
        $zonasConPorcentaje = $zonasConPorcentaje->filter(function ($zona) {
            return $zona->porcentaje > 15;
        });

        // 4. Datos para el gráfico de Chart.js
        $labels = $zonasConPorcentaje->pluck('zona_geografica')->toArray(); // HUECO K: pluck()
        $data   = $zonasConPorcentaje->pluck('total')->toArray();           // HUECO L: pluck()

        return view('reports.zonas', compact(
            'zonasConPorcentaje', 'totalGeneral', 'labels', 'data'
        ));
    }

    /**
     * REPORTE 2 — Interacciones por Asesor
     *
     * Partimos de la tabla 'users' (asesores) y hacemos LEFT JOINs
     * hacia 'clients' e 'interactions' para que aparezcan TODOS los
     * asesores aunque no tengan clientes ni interacciones.
     * Usamos COUNT(CASE WHEN...) para desglosar por tipo de interacción.
     *
     * RETO: también incluye el desglose por día de la semana.
     */
    public function interaccionesPorAsesor()
    {
        // Iniciamos desde 'users' para garantizar que aparezcan todos los asesores
        $asesores = DB::table('users')                          // HUECO A: tabla 'users'
            ->select(
                'users.id',
                'users.name',
                // HUECO B: contar solo las filas donde tipo = 'Llamada'
                DB::raw("COUNT(CASE WHEN interactions.tipo_interaccion = 'Llamada' THEN 1 END) AS llamadas"),
                // HUECO C: contar solo las filas donde tipo = 'Visita'
                DB::raw("COUNT(CASE WHEN interactions.tipo_interaccion = 'Visita' THEN 1 END) AS visitas"),
                // HUECO D: contar solo las filas donde tipo = 'WhatsApp'
                DB::raw("COUNT(CASE WHEN interactions.tipo_interaccion = 'WhatsApp' THEN 1 END) AS whatsapp"),
                // HUECO E: total general de interacciones del asesor
                DB::raw('COUNT(interactions.id) AS total')
            )
            // HUECO F: LEFT JOIN hacia clients (para que asesores sin clientes aparezcan)
            ->leftJoin('clients', 'clients.user_id', '=', 'users.id')
            // HUECO G: LEFT JOIN hacia interactions (para que asesores sin interacciones aparezcan)
            ->leftJoin('interactions', 'interactions.client_id', '=', 'clients.id')
            // HUECO H, I: agrupar por id y name del asesor (MySQL strict mode requiere ambos)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')                              // HUECO J: de mayor a meno
            ->get();

        // Extraer columnas para Chart.js con pluck()
        $labels   = $asesores->pluck('name')->toArray();       // HUECO K
        $llamadas = $asesores->pluck('llamadas')->toArray();    // HUECO L
        $visitas  = $asesores->pluck('visitas')->toArray();     // HUECO M
        $whatsapp = $asesores->pluck('whatsapp')->toArray();    // HUECO N

        // RETO DE MODIFICACIÓN: desglose por día de la semana usando DAYNAME()
        $porDia = DB::table('interactions')
            ->select(
                DB::raw("DAYNAME(fecha_seguimiento) AS dia"),
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy(DB::raw('DAYOFWEEK(fecha_seguimiento)'), DB::raw('DAYNAME(fecha_seguimiento)'))
            ->orderBy(DB::raw('DAYOFWEEK(fecha_seguimiento)'))
            ->get();

        return view('reports.interacciones', compact(
            'asesores', 'labels', 'llamadas', 'visitas', 'whatsapp', 'porDia'
        ));
    }
}
