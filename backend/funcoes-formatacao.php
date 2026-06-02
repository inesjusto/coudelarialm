<?php

function normalizarValorMonetario($valor) {
    $valor = trim((string)$valor);

    if ($valor === '') {
        return 0;
    }

    $valor = str_replace('€', '', $valor);
    $valor = str_replace(' ', '', $valor);

    /*
        Aceita:
        25
        25,00
        25.00
        1.000,00
        1000.00
    */

    $temVirgula = strpos($valor, ',') !== false;
    $temPonto = strpos($valor, '.') !== false;

    if ($temVirgula && $temPonto) {
        // Exemplo português: 1.000,00
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif ($temVirgula && !$temPonto) {
        // Exemplo: 25,00
        $valor = str_replace(',', '.', $valor);
    } elseif ($temPonto && !$temVirgula) {
        /*
            Pode ser:
            25.00 = decimal
            1.000 = milhar
        */
        $partes = explode('.', $valor);

        if (count($partes) === 2 && strlen($partes[1]) === 3) {
            $valor = str_replace('.', '', $valor);
        }
    }

    if (!is_numeric($valor)) {
        return 0;
    }

    return (float)$valor;
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