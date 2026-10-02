<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes por Zona — CRM Gestión Integral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h1 class="mb-1">📊 Clientes por Zona Geográfica</h1>
    <p class="text-muted mb-4">Solo se muestran zonas con más del 15% del total (Reto de modificación).</p>

    {{-- Tarjetas resumen --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-bg-primary">
                <div class="card-body">
                    <h6>Total de Clientes (general)</h6>
                    <h2>{{ $totalGeneral }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h6>Zonas con &gt; 15% del total</h6>
                    <h2>{{ $zonasConPorcentaje->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla detalle --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Detalle por Zona</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Zona</th>
                        <th>Total</th>
                        <th>%</th>
                        <th>Barra visual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zonasConPorcentaje as $i => $zona)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $zona->zona_geografica }}</strong></td>
                        <td><span class="badge bg-primary fs-6">{{ $zona->total }}</span></td>
                        <td>{{ $zona->porcentaje }}%</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: {{ $zona->porcentaje }}%">
                                    {{ $zona->porcentaje }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Gráfico de barras --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white"><h5 class="mb-0">Gráfico</h5></div>
        <div class="card-body">
            <canvas id="grafico" height="80"></canvas>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('reportes.interacciones') }}" class="btn btn-primary">
            Siguiente reporte →
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('grafico'), {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Clientes por Zona',
                data: @json($data),
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1']
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
</script>

</body>
</html>
