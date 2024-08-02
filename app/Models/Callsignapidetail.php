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
        
        $body_content = [ 'key' => $payload->key, 'station_id' => $payload->station_id, 'fetchfromid' => $goalpost];
        $json_body = json_encode($body_content);

        //get data from Wavelog
        $response = Http::acceptJson()->withBody($json_body , $bodytype)->post($this->url);

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
        $qso_count = $response['exported_qsos'];
        $newgoalpost = $response['lastfetchedid'];
        $adif_content = $response['adif'];

        //dont create upload for 0 QSOs
        if($qso_count < 1)
        {
            return null;
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