<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use j4nr6n\ADIF\Parser;

class Callsignapidetail extends Model
{
    use HasFactory;

    public function callsign() : BelongsTo
    {
        return $this->belongsTo(Callsign::class, "callsign_id", "id");
    }

    public function contextuser() : HasOne
    {
        return $this->hasOne(User::class, 'id', 'context_userid');
    }

    public function errorlogs() : HasMany
    {
        return $this->hasMany(Callsignapierrorlog::class);
    }

    public function uploads() : HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function pull() : ?Upload
    {
        //call each specific API implementation depending on the api type
        switch ($this->type) {
            case 'wavelog':
                return $this->pull_wavelog();
            default:
                return null;
        }
    }

    public function pull_wavelog() : ?Upload
    {
        //define minimal data in payload
        $minimal_keys = ['key', 'station_id'];
        
        //get payload from model
        $payload = json_decode($this->payload, true);
        
        //check if at least the minimal data fields are there
        $missingFields = array_diff($minimal_keys, array_keys($payload));

        //create error log for missing fields in payload
        if(!empty($missingFields))
        {
            $this->createerrorlog(500, "Missing fields in payload: " . implode(", ", $missingFields));
            return null;
        }

        //get goalpost from model
        if(is_numeric(($this->goalpost ?? 0)))
        {
            $goalpost = (int)$this->goalpost;
        }else
        {
            $this->createerrorlog(500, "Goalpost must be a number.");
            return null;
        }

        //set json body type
        $bodytype = "application/json";
        
        //create minimum payload content
        $payload_content = [ 'key' => $payload['key'], 'station_id' => $payload['station_id'], 'fetchfromid' => $goalpost ];

        //if optional limit is specified and a number, add data to payload content
        if(array_key_exists("limit", $payload))
        {
            if(is_numeric($payload['limit']))
            {
                $limit = max((int)$payload['limit'], -1);
                $payload_content['limit'] = $limit == 0 ? -1 : $limit;
            }
        }

        //convert content to json
        $json_body = json_encode($payload_content);

        //get data from Wavelog
        $response = null;
        try {
            $response = Http::acceptJson()->withBody($json_body , $bodytype)->post($this->url);
        } catch (\Throwable $th) {
            $this->createerrorlog($response->status(), 'Could not successfully pull data from ' . $this->url);
            return $response;
        }

        //react to response status
        switch ($response->status()) {
            case 200:
                //just continue
                break;
            case 400:
                $this->createerrorlog($response->status(), $response['reason']);
                return null;
            case 401:
                $this->createerrorlog($response->status(), $response['reason']);
                return null;
            case 404:
                $this->createerrorlog($response->status(), 'Page not found');
                return null;
            case 500:
                $this->createerrorlog($response->status(), 'Internal server error');
                return null;
            default:
                $this->createerrorlog($response->status(), 'Something unexpected');
                return null;
        }

        //get new goalpost from api
        try {
            $qso_count = $response['exported_qsos'];
            $newgoalpost = $response['lastfetchedid'];
            $adif_content = $response['adif'];
        } catch (\Throwable $th) {
            $this->createerrorlog(500, 'Cannot fetch needed info from response. Missing fields!');
            return null;
        }
        
        //dont create upload for 0 QSOs, but create a dummydownload to differentiate from an error during processing
        if($qso_count < 1)
        {
            //create dummy
            $dummyupload = new Upload();
            $dummyupload->overall_qso_count = 0;

            //set new goalpost
            $this->goalpost = strval($newgoalpost);
            $this->save();

            //return dummy
            return $dummyupload;
        }

        //check if ADIF lives inside the callsign validity period for at least 1 QSO
        //we can do this here because we know Wavelog provides the relevant fields inside the ADIF
        //return dummyupload to differentiate from a general error during processing
        //return -1 QSOs to signal validity error
        if(!db4scw_checkadifinsidevalidityperiod((new Parser())->parse($adif_content), $this->callsign))
        {
            //create dummy
            $dummyupload = new Upload();
            $dummyupload->overall_qso_count = -1;
            
            //set new goalpost
            $this->goalpost = strval($newgoalpost);
            $this->save();

            //return dummy
            return $dummyupload;
        }

        //save data to uplaod and proess data
        $upload = $this->saveupload('Wavelog API', $qso_count, $adif_content);

        //save info in API config
        $this->last_run = \Carbon\Carbon::now();
        $this->goalpost = strval($newgoalpost);
        $this->save();
     
        //return $upload;
        return $upload;
    }

    public function saveupload($type, $qso_count, $adif_content) : Upload
    {
        //create a new upload record
        $upload = new Upload();
        $upload->uploader_id = $this->contextuser->id;
        $upload->callsign_id = $this->callsign->id;
        $upload->file_content = $adif_content;
        $upload->overall_qso_count = $qso_count;
        $upload->type = $type;
        $upload->callsignapidetail_id = $this->id;
        $upload->save();

        //process upload, take operator from adif and ignore duplicates
        $correct = $upload->process(null, true);

        //write data to callsign
        $upload->callsign->setlastupload();

        return $upload;
    }

    public function createerrorlog(int $statuscode, string $message) : Callsignapierrorlog
    {
        //create errorlog and return
        $log = new Callsignapierrorlog();
        $log->callsignapierrorlogs = $this->id;
        $log->statuscode = $statuscode;
        $log->message = $message;
        $log->save();
        return $log;
    }
}
