<?php

function getcallsignwithoutadditionalinfo(string $input) : string
{
    $ergebnis = strtoupper($input);
    $ergebnis = preg_replace("/^[A-Z, 0-9]{1,2}\//", "", $ergebnis); //delete prefix
    $ergebnis = preg_replace("/\/\w{0,}$/", "", $ergebnis); //delete suffix
    
    //return pure callsign
    return $ergebnis;
}

function swolf_validatorerrors(\Illuminate\Validation\Validator $validator) : string
{
    return implode(" | ", $validator->errors()->all());
}

function swolf_getawardmodetext(int $mode, $threshold = null) : string
{
    switch ($mode) {
        case 0:
            return "Each QSO counts. (min: " . ($threshold ?? 0) . ")";
        case 1:
            return "Each distinct event callsign counts for 1 QSO. (min: " . ($threshold ?? 0) . ")";
        case 2:
            return "Each callsign counts 1 on each mode. (min: " . ($threshold ?? 0) . ")";
        case 3:
            return "Each callsign counts 1 each band. (min: " . ($threshold ?? 0) . ")";
        case 4:
            return "Each callsign counts 1 on each band and each mode. (min: " . ($threshold ?? 0) . ")";
        case 5:
            return "Each callsign counts 1 on each mainmode (CW, VOICE, DIGITAL) again (min: " . ($threshold ?? 0) . ")";
        case 6:
            return "Each callsign counts 1 on each band and each mainmode (CW, VOICE, DIGITAL). (min: " . ($threshold ?? 0) . ")";
        default:
            return "error";
    }
}

function swolf_getmaxmode() : int
{
    return 6;
}