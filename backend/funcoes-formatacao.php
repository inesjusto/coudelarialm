<?php

function normalizarValorMonetario($valor) {
    $valor = trim((string)$valor);

    if ($valor === '') {
        return 0;
    }

    $valor = str_replace('€', '', $valor);
    $valor = str_replace(' ', '', $valor);

    $temVirgula = strpos($valor, ',') !== false;
    $temPonto = strpos($valor, '.') !== false;

    if ($temVirgula && $temPonto) {
        // Formato português: 1.000,00
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif ($temVirgula && !$temPonto) {
        // Formato português simples: 25,00
        $valor = str_replace(',', '.', $valor);
    } elseif ($temPonto && !$temVirgula) {
        // Pode ser decimal inglês: 25.00 ou milhar português: 1.000
        $partes = explode('.', $valor);

        if (count($partes) === 2 && strlen($partes[1]) === 3) {
            $valor = str_replace('.', '', $valor);
        }
    }

    return is_numeric($valor) ? (float)$valor : 0;
}

function normalizarNumeroDecimal($valor) {
    $valor = trim((string)$valor);

    if ($valor === '') {
        return 0;
    }

    $valor = str_replace(' ', '', $valor);

    $temVirgula = strpos($valor, ',') !== false;
    $temPonto = strpos($valor, '.') !== false;

    if ($temVirgula && $temPonto) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif ($temVirgula && !$temPonto) {
        $valor = str_replace(',', '.', $valor);
    } elseif ($temPonto && !$temVirgula) {
        $partes = explode('.', $valor);

        if (count($partes) === 2 && strlen($partes[1]) === 3) {
            $valor = str_replace('.', '', $valor);
        }
    }

    return is_numeric($valor) ? (float)$valor : 0;
}

function formatarValorEuros($valor) {
    return number_format((float)$valor, 2, ',', '.') . ' €';
}

function formatarValorInput($valor) {
    if ($valor === null || $valor === '') {
        return '';
    }

    return number_format((float)$valor, 2, ',', '.');
}

function formatarNumeroInput($valor, $casasDecimais = 2) {
    if ($valor === null || $valor === '') {
        return '';
    }

    return number_format((float)$valor, $casasDecimais, ',', '.');
}
?>
