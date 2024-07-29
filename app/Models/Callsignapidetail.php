<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function pull() : int
    {
        //call each specific API implementation depending on the api type
        switch ($this->type) {
            case 'wavelog':
                return $this->pull_wavelog();
            default:
                return -1;
        }
    }

    public function pull_wavelog() : int
    {
        
        //get payload from moddel
        $payload = json_decode($this->payload);
        $goalpost = (int)$this->goalpost;

        $response = Http::post($this->url, [
            'key' => $payload['key'],
            'station_id' => $payload['station_id'], 
            'goalpost' => $goalpost
        ]);

        //get new goalpost from api
        $newgoalpost = $response['newgoalpost'];
        $adif_content = $response['adif'];

        //save data to uplaod and proess data
        $correct_qsos = $this->saveupload($adif_content, $newgoalpost);
     
        //return correct QSO count
        return $correct_qsos;
    }

    public function saveupload($adif_content, $newgoalpost) : int
    {
        //create a new upload record
        $upload = new Upload();
        $upload->uploader_id = $this->contextuser->id;
        $upload->callsign_id = $this->callsign->id;
        $upload->file_content = $adif_content;
        $upload->overall_qso_count = 0;
        $upload->type = 'Wavelog API';
        $upload->save();

        //process upload
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

        return $correct;
    }
}
