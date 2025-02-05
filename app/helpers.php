<?php
use App\Models\Autoimport;
use App\Models\Callsign;
use App\Models\Dxcc;

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

function db4scw_getdxcc(string $callsign) : Dxcc {
        
    //load info from API - return dummy answer in case API does not answer
    try {
        $dxccinfo = file_get_contents("https://www.hamqth.com/dxcc.php?callsign=" . urlencode($callsign));
    } catch (\Throwable $th) {
        $dummyanswer = new Dxcc();
        $dummyanswer->dxcc = -1;
        return $dummyanswer;
    }
    
    //read XML anser
    $xmlObject = simplexml_load_string($dxccinfo);
    
    //get ADIF info - return dummy answer in case API does not provide the expected information
    try {
        $adif = (integer)$xmlObject->dxcc->adif;
    } catch (\Throwable $th) {
        $dummyanswer = new Dxcc();
        $dummyanswer->dxcc = -2;
        return $dummyanswer;
    }
    
    //Load DXCC Model - return unknown DXCC if there is something wrong
    $dxcc = Dxcc::where('dxcc', $adif)->first(); 
    return $dxcc == null ? Dxcc::where('dxcc', 0)->first() : $dxcc;
}

function db4scw_getdxcc_wavelog(string $callsign, string $wavelog_server, string $wavelog_key ) : Dxcc 
{
    //create options and API payload
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode([ 'key' => $wavelog_key, 'callsign' => $callsign ]),
            'timeout' => 5, // Set timeout to 5 seconds
        ]
    ];

    //set context
    $context = stream_context_create($options);

    try {
        $response = file_get_contents($wavelog_server, false, $context);
        if ($response === FALSE) {
            throw new \Exception("DXCC lookup failed.");
        }
        $httpResponseHeader = $http_response_header[0] ?? '';
        if ($httpResponseHeader != 200) {
            throw new \Exception("DXCC lookup failed.");
        }
    } catch (\Exception $e) {
        if ($response === FALSE) {
            $dummyanswer = new Dxcc();
            $dummyanswer->dxcc = -3;
            return $dummyanswer;

        }
    }

    //get data from response
    $data = json_decode($response, true);

    //extract adif dxcc_id
    $dxcc_id = $data['dxcc_id'];

    //Load DXCC Model - return unknown DXCC if there is something wrong
    $dxcc = Dxcc::where('dxcc', $dxcc_id)->first(); 
    return $dxcc == null ? Dxcc::where('dxcc', 0)->first() : $dxcc;
}

function db4scw_determine_dxcc_api_mode() : string
{
    //get environment variables
    $wavelog_url = env('WAVELOG_URL');
    $wavelog_key = env('WAVELOG_API_KEY');

    //determine mode - use Wavelog if environment variables are existent, as well as filled
    $use_wavelog = false;
    if($wavelog_key != null and $wavelog_url != null)
    {
        if(strlen($wavelog_url) > 0 and strlen($wavelog_key) > 0)
        {
            $use_wavelog = true;
        }
    }

    //return API mode
    return $use_wavelog ? 'Wavelog' : 'HamQTH';
}