<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use j4nr6n\ADIF\Parser;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = array('type');

    public function contacts() : HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function start_date()
    {
        if($this->contacts->count() < 1)
        {
            return \Carbon\Carbon::parse('1900-01-01');
        }

        return $this->contacts->min('qso_datetime');
    }

    public function end_date()
    {
        if($this->contacts->count() < 1)
        {
            return \Carbon\Carbon::parse('1900-01-01');
        }

        return $this->contacts->max('qso_datetime');
    }

    public function uploader() : BelongsTo
    {
        return $this->belongsTo(User::class, "uploader_id", "id");
    }

    public function callsign() : BelongsTo
    {
        return $this->belongsTo(Callsign::class, 'callsign_id', 'id');
    }

    public function process(string $forcedoperator = null, bool $ignoreduplicates = false) : int
    {
        //load file content
        $data = (new Parser())->parse($this->file_content);

        //dummyfill null values
        $valid_from = $this->callsign->valid_from == null ? \Carbon\Carbon::parse('1900-01-01') : $this->callsign->valid_from;
        $valid_to = $this->callsign->valid_to == null ? \Carbon\Carbon::now()->addyears(99) : $this->callsign->valid_to;
        
        //prepare Error Collection
        $errors = [];

        //loop through each record
        $i = 0;
        $correct = 0;
        foreach ($data as $record) {

            //sanity check for parsed adif file
            $requiredfields = ['CALL', 'QSO_DATE', 'TIME_ON', 'FREQ', 'RST_SENT', 'RST_RCVD', 'MODE']; 

            $check = true;
            foreach ($requiredfields as $field) {
                
                if(!array_key_exists($field, $record))
                {
                    $check = false;
                    array_push($errors, 'Record ' . $i+1 . ' Is missing required field <' . $field . '>. Skipping.');
                }
            }

            //skip record if required fields are not present
            if(!$check)
            {
                $i++;
                continue;
            }

            //populate Contact
            $contact = new Contact();
            $contact->callsign_id = $this->callsign->id;
            $contact->upload_id = $this->id;
            $contact->qso_datetime = \Carbon\Carbon::parse($record['QSO_DATE'] . ' ' . substr($record['TIME_ON'],0,2) . ':' . substr($record['TIME_ON'], 2, 2));
            $contact->raw_callsign = $record['CALL'];
            $contact->callsign = swolf_getcallsignwithoutadditionalinfo($record['CALL']);
            $contact->freq = $record['FREQ'];
            $contact->rst_s = $record['RST_SENT'];
            $contact->rst_r = $record['RST_RCVD'];

            //check date validity
            if($contact->qso_datetime < $valid_from or $contact->qso_datetime > $valid_to)
            {
                array_push($errors, 'Record ' . $i+1 . ', Callsign ' . $contact->raw_callsign . ' on ' . $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC: Callsign is not valid for this QSO datetime. Skipping.');
                $i++;
                continue;
            }
            
            //operator handling
            $operator = "";
            if($forcedoperator != null)
            {
                $operator = strtoupper($forcedoperator);
            }else
            {
                if(array_key_exists('OPERATOR', $record))
                {
                    if($record['OPERATOR'] != null)
                    {
                        if(strlen($record['OPERATOR']) > 0)
                        {
                            $operator = strtoupper($record['OPERATOR']);
                        }else
                        {
                            $operator = $this->callsign->call;
                        }
                        
                    }else
                    {
                        $operator = $this->callsign->call;
                    }
                    
                }else
                {
                    $operator = $this->callsign->call;
                }
            }

            //set operator
            $contact->operator = $operator;

            //try to get Band, mode and DXCC
            $band = Band::where([['start', '<=', $contact->freq], ['end', '>=', $contact->freq]])->first();
            $mode = Mode::where('submode', $record['MODE'])->first();
            $dxcc = Dxcc::where('dxcc', array_key_exists('DXCC', $record) ? $record['DXCC'] : 0)->first();

            //Check for errors
            if($band == null)
            {
                array_push($errors, 'Record ' . $i+1 . ', Callsign ' . $contact->raw_callsign . ' on ' . $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC: Frequency ' . $contact->freq . ' is out of bandplan. Skipping.');
                $i++;
                continue;
            }

            if($mode == null)
            {
                array_push($errors, 'Record ' . $i+1 . ', Callsign ' . $contact->raw_callsign . ' on ' . $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC: Mode ' . $record['MODE'] . ' is not recognised. Skipping.');
                $i++;
                continue;
            }

            if($dxcc == null)
            {
                $contact->dxcc_id = Dxcc::where('dxcc', 0)->first();
            }

            //insert band, mode and dxcc to contact
            $contact->band_id = $band->id;
            $contact->mode_id = $mode->id;
            $contact->dxcc_id = $dxcc->id;

            //duplicate-check
            $alreadythere = Contact::where([['callsign_id', $contact->callsign_id], ['qso_datetime', $contact->qso_datetime], ['callsign', $contact->callsign], ['band_id', $contact->band_id]]);

            if($alreadythere->count() > 0)
            {
                if(!$ignoreduplicates)
                {
                    array_push($errors, 'Record ' . $i+1 . ', Callsign ' . $contact->raw_callsign . ' on ' . $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC: QSO already exists in the database. Skipping.');
                }
                
                $i++;
                continue;
            }

            //save the contact
            $contact->save();
            
            //increment counters
            $i++;
            $correct++;

        }

        //write errors and statistics to database
        $this->errors = count($errors) > 0 ? implode("|", $errors) : '';

        //Save upload record
        $this->save();
        
        return $correct;
    }
}
