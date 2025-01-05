<?php
use App\Models\Autoimport;
use App\Models\Callsign;

function db4scw_getcallsignwithoutadditionalinfo(string $input) : string
{
    $result = strtoupper($input);
    $result = preg_replace("/^[A-Z, 0-9]{1,3}\//", "", $result); //delete prefix
    $result = preg_replace("/\/\w{0,}$/", "", $result); //delete suffix
    
    //return pure callsign
    return $result;
}

function db4scw_getcallsignsfromstring(?string $input)
{
    return explode(",", db4scw_sanitizecallsignstring($input) ?? '');
}

function db4scw_sanitizecallsignstring(?string $input) : ?string
{
    if($input == null) { return null; }
    $callsignstrings_raw = str_replace(" ", "", $input);
    $callsignstrings_raw = str_replace(";", ",", $callsignstrings_raw);
    $callsignstrings_raw = str_replace("-", ",", $callsignstrings_raw);
    $callsignstrings_raw = str_replace("/", ",", $callsignstrings_raw);
    $callsignstrings_raw = str_replace("\\", ",", $callsignstrings_raw);
    $callsignstrings_raw = str_replace("_", ",", $callsignstrings_raw);
    return strtoupper($callsignstrings_raw);
}

function db4scw_validatorerrors(\Illuminate\Validation\Validator $validator) : string
{
    return implode(" | ", $validator->errors()->all());
}

function db4scw_getawardmodetext(int $mode, $threshold = null) : string
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
        case 7:
            return "Each callsign of the chosen dxcc counts 1. (min: " . ($threshold ?? 0) . ")";
        case 8:
            return "Each callsign of the chosen continent counts 1. (min: " . ($threshold ?? 0) . ")";
        case 9:
            return "Any number of QSOs inside one defined award subtimeframe counts as 1. (min: " . ($threshold ?? 0) . ")";
        default:
            return "error";
    }
}

function db4scw_getmaxmode() : int
{
    return 9;
}

function db4scw_getAutoImportFieldContent(Autoimport $conf, string $field, stdClass $record) : ?string
{
    //check null-field
    if($field == null)
    {
        return null;
    }

    //load classes as arrays
    $confarray = $conf->getAttributes();
    $recordarray = get_object_vars($record);

    //get field input
    $fieldinput = $confarray[$field];

    //check for fixed input and return that
    if(preg_match("/^'.{1,50}'$/", $fieldinput))
    {
        $x = preg_match('/\'([^\']*)\'/', $fieldinput, $output_array);
        return $output_array[1];
    }

    //check if database field exists
    if(!array_key_exists($fieldinput, $recordarray))
    {
        return null;
    }

    //return database value
    return $recordarray[$fieldinput];

}

function db4scw_checkadifinsidevalidityperiod($data, Callsign $callsign) : bool
{
    //get first and last QSO and parse datetime of these records
    $last_qso = collect($data)->sortBy([['QSO_DATE', 'desc'], ['TIME_ON', 'desc']])->first();
    $first_qso = collect($data)->sortBy([['QSO_DATE', 'asc'], ['TIME_ON', 'asc']])->first();
    $first_qso_datetime = \Carbon\Carbon::parse($first_qso['QSO_DATE'] . ' ' . substr($first_qso['TIME_ON'],0,2) . ':' . substr($first_qso['TIME_ON'], 2, 2));
    $last_qso_datetime = \Carbon\Carbon::parse($last_qso['QSO_DATE'] . ' ' . substr($last_qso['TIME_ON'],0,2) . ':' . substr($last_qso['TIME_ON'], 2, 2));

    //dummyfill null values on callsign validity
    $valid_from = $callsign->valid_from == null ? \Carbon\Carbon::parse('1900-01-01') : $callsign->valid_from;
    $valid_to = $callsign->valid_to == null ? \Carbon\Carbon::now()->addyears(99) : $callsign->valid_to;

    //check if ADIF data lives completely outside of the validity of the callsign
    if($last_qso_datetime < $valid_from or $first_qso_datetime > $valid_to)
    {
        return false;
    }

    //check ok
    return true;
}

function stalinsort(array $array, bool $reverse = false) : array {
    
    //if array is empty, return empty array
    if (empty($array)) 
    {
        return [];
    }

    //only add elements that are already sorted to the array, eliminate the rest of the elements
    $sortedArray = [];

    foreach ($array as $element) 
    {

        //first element is always fine
        if(empty($sortedArray))
        {
            $sortedArray[] = $element;
            continue;
        }

        //only add element if greater or equal than the last one
        if ($element >= end($sortedArray)) {
            $sortedArray[] = $element;
        }
    }

    //return result, reverse if needed
    return $reverse ? array_reverse($sortedArray) : $sortedArray;
}

function parse(string $input, $serial_number_present = false) : array
    {
        //split the input into lines
        $lines = explode("\n", trim($input));

        //initialize the result array
        $qso_lines_raw = [];
        $header = [];

        //helper variable to determine common 59 element indices in QSO lines
        $common_59_indices = null;

        //flag to indicate processing mode
        $qso_mode = false;

        //loop through each line
        foreach ($lines as $line) {

            //if we encounter "QSO" or "X-QSO" switch processing mode to QSO mode
            if (strpos($line, 'QSO:') === 0 or strpos($line, 'X-QSO:') === 0) {
                $qso_mode = true;
            }else {
                $qso_mode = false;
            }

            //if we encounter "END-OF-LOG", stop processing lines
            if (strpos($line, 'END-OF-LOG') === 0) {
                break;
            }

            //process and collect header lines if qso mode is not set
            if (!$qso_mode) {
                
                //split the line into an array using ': ' as the delimiter
                $parts = explode(': ', $line, 2);

                //collect header information
                $header[$parts[0]] = $parts[1];

                //skip to next line
                continue;
            }

            //process and collect QSO lines if qso mode is set
            if ($qso_mode) {
                
                //split the line into the elements
                $qso_elements = preg_split('/\s+/', trim($line));
                
                //add qso elements to qso line array
                array_push($qso_lines_raw, $qso_elements);

                //find all occurrences of "59"
                $indices_of_59 = [];
                foreach ($qso_elements as $index => $value) {
                    if ($value === "59" or $value === "599") {
                        $indices_of_59[] = $index;
                    }
                }

                //find common indices
                if ($common_59_indices === null) {
                    //initialize common indices on the first iteration
                    $common_59_indices = $indices_of_59;
                } else {
                    //intersect with current indices, preserving only common indices
                    $common_59_indices = array_intersect($common_59_indices, $indices_of_59);
                }
                
                //skip to next line
                continue;
            }
        }

        //abort further processing if no qso lines were found, return header only
        if(count($qso_lines_raw) < 1)
        {
            $result = [];
            $result["HEADER"] = $header;
            $result["QSOS"] = [];
            $result["SENT_59_POS"] = 0;
            $result["RCVD_59_POS"] = 0;
            $result["SENT_EXCHANGE_COUNT"] = 0;
            $result["RCVD_EXCHANGE_COUNT"] = 0;
    
            //return result
            return $result;
        }

        //get positions of 59s inside QSO lines
        $sent_59_pos = min($common_59_indices);
        $rcvd_59_pos = max($common_59_indices);

        //using 59 positions, remake qso_lines
        $qso_lines = [];

        //change all QSOs into associative arrays with meaningful keys
        foreach ($qso_lines_raw as $line) {
            
            $qso_line = [];
            
            //get well defined fields
            $qso_line["QSO_MARKER"] = $line[0];
            $qso_line["FREQ"] = $line[1];
            $qso_line["MODE"] = $line[2];
            $qso_line["DATE"] = $line[3];
            $qso_line["TIME"] = $line[4];
            $qso_line["OWN_CALLSIGN"] = $line[5];
            $qso_line["SENT_59"] = $line[$sent_59_pos];
           
            //set serial if requested
            if($serial_number_present)
            {
                $qso_line["SENT_SERIAL"] = $line[$sent_59_pos + 1];
            }

            //get all remaining sent exchanges
            $exchange_nr = 1;
            $startindex = ($sent_59_pos + ($serial_number_present ? 2 : 1));
            $endindex = ($rcvd_59_pos - 1);
            for ($i = $startindex; $i < $endindex; $i++) { 
                $qso_line["SENT_EXCH_" . $exchange_nr] = $line[$i];
                $exchange_nr++;
            }

            //get rest of the well defined fields
            $qso_line["RCVD_CALLSIGN"] = $line[$rcvd_59_pos - 1];
            $qso_line["RCVD_59"] = $line[$rcvd_59_pos];

            //set serial if requested
            if($serial_number_present)
            {
                $qso_line["RCVD_SERIAL"] = $line[$rcvd_59_pos + 1];
            }

            //get all remaining received exchanges
            $exchange_nr = 1;
            $startindex = ($rcvd_59_pos + ($serial_number_present ? 2 : 1));
            $endindex = (count($line));
            for ($i = $startindex; $i < $endindex; $i++) { 
                $qso_line["RCVD_EXCH_" . $exchange_nr] = $line[$i];
                $exchange_nr++;
            }

            //collect new associative array
            array_push($qso_lines, $qso_line);
        }

        //construct result, including positions of 59s for further processing down the line
        $result = [];
        $result["HEADER"] = $header;
        $result["QSOS"] = $qso_lines;
        $result["SENT_59_POS"] = $sent_59_pos;
        $result["RCVD_59_POS"] = $rcvd_59_pos;
        $result["SENT_EXCHANGE_COUNT"] = $rcvd_59_pos - $sent_59_pos - ($serial_number_present ? 3 : 2);
        $result["RCVD_EXCHANGE_COUNT"] = count($qso_lines[0]) - 1 - $rcvd_59_pos - ($serial_number_present ? 1 : 0);

        //return result
        return $result;
    }