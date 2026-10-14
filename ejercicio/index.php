<?php
// Calculo de sueldo quincenal con retenciones (Ahorro Habitacional y Seguro Social)

const TARIFA_DIURNA     = 675.00;
const TARIFA_VESPERTINA = 700.00;
const TARIFA_NOCTURNA   = 956.23;

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function bs(float $n): string {
    return number_format($n, 2, ',', '.') . ' Bs.';
}

/** Devuelve [porcentaje ahorro habitacional, porcentaje seguro social] según el sueldo bruto. */
function porcentajes(float $bruto): array {
    if ($bruto < 85000) {
        return [0.10, 0.15];
    }
    if ($bruto <= 150000) {
        return [0.15, 0.20];
    }
    return [0.30, 0.25];
}

$errores   = [];
$resultado = null;
$old = [
    'nombre'      => '',
    'cedula'      => '',
    'h_diurna'    => '',
    'h_vespertina'=> '',
    'h_nocturna'  => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim((string)($_POST[$k] ?? ''));
    }

    if ($old['nombre'] === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if (!preg_match('/^\d{5,9}$/', $old['cedula'])) {
        $errores[] = 'La cédula debe contener entre 5 y 9 dígitos numéricos.';
    }

    $horas = [];
    foreach (['h_diurna' => 'diurnas', 'h_vespertina' => 'vespertinas', 'h_nocturna' => 'nocturnas'] as $campo => $etq) {
        $v = str_replace(',', '.', $old[$campo]);
        if ($v === '' || !is_numeric($v) || (float)$v < 0) {
            $errores[] = "Las horas $etq deben ser un número mayor o igual a 0.";
        } else {
            $horas[$campo] = (float)$v;
        }
    }

    if (!$errores) {
        $sub_d = $horas['h_diurna']     * TARIFA_DIURNA;
        $sub_v = $horas['h_vespertina'] * TARIFA_VESPERTINA;
        $sub_n = $horas['h_nocturna']   * TARIFA_NOCTURNA;
        $bruto = $sub_d + $sub_v + $sub_n;

        [$pAh, $pSS] = porcentajes($bruto);
        $ah   = $bruto * $pAh / 100;
        $ss   = $bruto * $pSS / 100;
        $neto = $bruto - $ah - $ss;

        $resultado = compact('sub_d', 'sub_v', 'sub_n', 'bruto', 'pAh', 'pSS', 'ah', 'ss', 'neto') + [
            'horas' => $horas,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sueldo Quincenal</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: system-ui, Arial, sans-serif; background: #e5c0dc; margin: 0; padding: 24px; color: #222; }
    .contenedor { max-width: 760px; margin: 0 auto; }
    h1 { font-size: 1.6rem; margin-bottom: 4px; }
    .card { background: #fff; border-radius: 10px; padding: 20px 24px; margin-top: 18px; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
    .fila { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .fila3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 4px; }
    input { width: 100%; padding: 9px 10px; border: 1px solid #c4c9d1; border-radius: 6px; font-size: 1rem; }
    button { margin-top: 18px; padding: 10px 22px; background: #c51a7e; color: #fff; border: 0; border-radius: 6px; font-size: 1rem; cursor: pointer; }
    button:hover { background: #f390be; }
    .errores { background: #fde8e8; border: 1px solid #f5b5b5; color: #8a1f1f; padding: 10px 16px; border-radius: 6px; margin-top: 18px; }
    .errores ul { margin: 0; padding-left: 18px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #e3e6ea; }
    td.num, th.num { text-align: right; }
    tr.total td { font-weight: 700; background: #f7f9fb; }
    tr.neto td { font-weight: 700; font-size: 1.15rem; background: #e6f4ea; }
    .datos p { margin: 4px 0; }
    @media (max-width: 600px) { .fila, .fila3 { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="contenedor">
    <h1>Cálculo de sueldo quincenal</h1>
    <p>Ingrese los datos del trabajador y las horas trabajadas en cada turno.</p>

    <?php if ($errores): ?>
        <div class="errores"><ul>
            <?php foreach ($errores as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form method="post" class="card" novalidate>
        <div class="fila">
            <div>
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" value="<?= h($old['nombre']) ?>" required>
            </div>
            <div>
                <label for="cedula">Cédula</label>
                <input id="cedula" name="cedula" inputmode="numeric" value="<?= h($old['cedula']) ?>" required>
            </div>
        </div>
        <div class="fila3" style="margin-top:14px">
            <div>
                <label for="h_diurna">Horas diurnas (<?= bs(TARIFA_DIURNA) ?>/h)</label>
                <input id="h_diurna" name="h_diurna" type="number" min="0" step="0.01" value="<?= h($old['h_diurna']) ?>" required>
            </div>
            <div>
                <label for="h_vespertina">Horas vespertinas (<?= bs(TARIFA_VESPERTINA) ?>/h)</label>
                <input id="h_vespertina" name="h_vespertina" type="number" min="0" step="0.01" value="<?= h($old['h_vespertina']) ?>" required>
            </div>
            <div>
                <label for="h_nocturna">Horas nocturnas (<?= bs(TARIFA_NOCTURNA) ?>/h)</label>
                <input id="h_nocturna" name="h_nocturna" type="number" min="0" step="0.01" value="<?= h($old['h_nocturna']) ?>" required>
            </div>
        </div>
        <button type="submit">Calcular</button>
    </form>

    <?php if ($resultado): $r = $resultado; ?>
    <div class="card datos">
        <h2>Datos del empleado</h2>
        <p><strong>Nombre:</strong> <?= h($old['nombre']) ?></p>
        <p><strong>Cédula:</strong> <?= h($old['cedula']) ?></p>
    </div>

    <div class="card">
        <h2>Recibo quincenal</h2>
        <table>
            <thead>
                <tr><th>Concepto</th><th class="num">Horas</th><th class="num">Tarifa</th><th class="num">Monto</th></tr>
            </thead>
            <tbody>
                <tr><td>Diurna</td><td class="num"><?= h((string)$r['horas']['h_diurna']) ?></td><td class="num"><?= bs(TARIFA_DIURNA) ?></td><td class="num"><?= bs($r['sub_d']) ?></td></tr>
                <tr><td>Vespertina</td><td class="num"><?= h((string)$r['horas']['h_vespertina']) ?></td><td class="num"><?= bs(TARIFA_VESPERTINA) ?></td><td class="num"><?= bs($r['sub_v']) ?></td></tr>
                <tr><td>Nocturna</td><td class="num"><?= h((string)$r['horas']['h_nocturna']) ?></td><td class="num"><?= bs(TARIFA_NOCTURNA) ?></td><td class="num"><?= bs($r['sub_n']) ?></td></tr>
                <tr class="total"><td colspan="3">Sueldo quincenal bruto</td><td class="num"><?= bs($r['bruto']) ?></td></tr>
                <tr><td colspan="3">Ahorro Habitacional (<?= rtrim(rtrim(number_format($r['pAh'], 2, ',', ''), '0'), ',') ?>%)</td><td class="num">- <?= bs($r['ah']) ?></td></tr>
                <tr><td colspan="3">Seguro Social (<?= rtrim(rtrim(number_format($r['pSS'], 2, ',', ''), '0'), ',') ?>%)</td><td class="num">- <?= bs($r['ss']) ?></td></tr>
                <tr class="neto"><td colspan="3">Sueldo quincenal neto</td><td class="num"><?= bs($r['neto']) ?></td></tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
