<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        //get payload from model
        $payload = json_decode($this->payload);
        $goalpost = (int)$this->goalpost;

        //create json body
        $bodytype = "application/json";
        
        $body_content = [ 'key' => $payload->key, 'station_id' => $payload->station_id, 'goalpost' => $goalpost];
        $json_body = json_encode($body_content);

        //get data from Wavelog
        $response = Http::acceptJson()->withBody($json_body , $bodytype)->post($this->url);

        switch ($response->status()) {
            case 200:
                //einfach weitermachen
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
        $qso_count = $response['exported_qsos'];
        $newgoalpost = $response['newgoalpost'];
        $adif_content = $response['adif'];

        //dont create upload for 0 QSOs
        if($qso_count < 1)
        {
            return null;
        }

        //save data to uplaod and proess data
        $upload = $this->saveupload('Wavelog API', $adif_content, $newgoalpost);
     
        //return $upload;
        return $upload;
    }

    public function saveupload($type, $adif_content, $newgoalpost) : Upload
    {
        //create a new upload record
        $upload = new Upload();
        $upload->uploader_id = $this->contextuser->id;
        $upload->callsign_id = $this->callsign->id;
        $upload->file_content = $adif_content;
        $upload->overall_qso_count = 0;
        $upload->type = $type;
        $upload->save();

        //process upload, take operator from adif and ignore duplicates
        $correct = $upload->process(null, true);

        //set qso count in upload object to match imported QSO Count
        $upload->overall_qso_count = $correct;
        $upload->save();

        //write data to callsign
        $upload->callsign->setlastupload();

        //save info in API config
        $this->last_run = \Carbon\Carbon::now();
        $this->goalpost = strval($newgoalpost);
        $this->save();

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
