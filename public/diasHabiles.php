<?php

function obtenerDiasHabiles($fechaInicio, $fechaFin, $incluirSabado = false) {
    $inicio = new DateTime($fechaInicio);
    $fin = new DateTime($fechaFin); 
    
    $fin->modify('+1 day');
    
    $intervalo = new DateInterval('P1D');
    $periodo = new DatePeriod($inicio, $intervalo, $fin);
    
    $diasHabiles = [];
    $anioActual = '';
    $festivosAnio = [];

    foreach ($periodo as $fecha) {
        $anio = $fecha->format('Y');
        
        if ($anio !== $anioActual) {
            $anioActual = $anio;
            $festivosAnio = obtenerFestivosColombia($anio);
        }

        $formatoFecha = $fecha->format('Y-m-d');
        $diaSemana = (int) $fecha->format('N'); // 1 (Lun) a 7 (Dom)

        // Si se incluye sábado (6), solo el domingo (7) es fin de semana.
        // Si no se incluye, sábado y domingo (>= 6) se consideran fin de semana.
        $esFinDeSemana = $incluirSabado ? ($diaSemana === 7) : ($diaSemana >= 6);
        $esFestivo = in_array($formatoFecha, $festivosAnio);

        if (!$esFinDeSemana && !$esFestivo) {
            $diasHabiles[] = $formatoFecha;
        }
    }

    return $diasHabiles;
}

function obtenerFestivosColombia($anio) {
    $festivos = [];

    $festivos[] = "$anio-01-01";
    $festivos[] = "$anio-05-01";
    $festivos[] = "$anio-07-20";
    $festivos[] = "$anio-08-07";
    $festivos[] = "$anio-12-08";
    $festivos[] = "$anio-12-25";

    $leyEmiliani = [
        "$anio-01-06", "$anio-03-19", "$anio-06-29", 
        "$anio-08-15", "$anio-10-12", "$anio-11-01", "$anio-11-11"
    ];

    foreach ($leyEmiliani as $fechaStr) {
        $fecha = new DateTime($fechaStr);
        if ($fecha->format('N') != 1) {
            $fecha->modify('next monday');
        }
        $festivos[] = $fecha->format('Y-m-d');
    }

    $pascuaTimestamp = easter_date((int)$anio);
    $pascua = new DateTime(date('Y-m-d', $pascuaTimestamp));

    $juevesSanto = clone $pascua; $juevesSanto->modify('-3 days');
    $festivos[] = $juevesSanto->format('Y-m-d');

    $viernesSanto = clone $pascua; $viernesSanto->modify('-2 days');
    $festivos[] = $viernesSanto->format('Y-m-d');

    $ascension = clone $pascua; $ascension->modify('+43 days')->modify('next monday');
    $festivos[] = $ascension->format('Y-m-d');

    $corpus = clone $pascua; $corpus->modify('+64 days')->modify('next monday');
    $festivos[] = $corpus->format('Y-m-d');

    $sagrado = clone $pascua; $sagrado->modify('+71 days')->modify('next monday');
    $festivos[] = $sagrado->format('Y-m-d');

    return $festivos;
}

// --- CAPTURAR PARÁMETROS DE LA URL ---
$fecha_inicial  = $_GET['fecha_inicial'] ?? null;
$fecha_final    = $_GET['fecha_final'] ?? null;

// Capturar 'incluir_sabado' (acepta true, 1 o "1")
$incluir_sabado = filter_var($_GET['incluir_sabado'] ?? false, FILTER_VALIDATE_BOOLEAN);

$dias_habiles = obtenerDiasHabiles($fecha_inicial, $fecha_final, $incluir_sabado);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'fecha_inicial'      => $fecha_inicial,
    'fecha_final'        => $fecha_final,
    'incluye_sabado'     => $incluir_sabado,
    'total_dias_habiles' => count($dias_habiles),
    'dias_habiles'       => $dias_habiles
], JSON_PRETTY_PRINT);