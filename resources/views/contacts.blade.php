<x-layout>
    <x-slot name="title">Contacts for Upload {{ $upload->id }}</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <h1 class="text-center mb-4">Contacts for Upload {{ $upload->id }}:</h1>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
              <thead class="thead-light">
                  <tr>
                      <th>Eventcallsign</th>
                      <th>Operator</th>
                      <th>QSO Date and Time</th>
                      <th>Mode</th>
                      <th>Callsign (with QRZ-Link)</th>
                      <th>Frequency</th>
                      <th>Band</th>
                  </tr>
              </thead>
              <tbody>
                  @if($contacts->count() < 1)
                      <td colspan="7" style="text-align: center; ">There are no registered QSOs for this callsign yet. Try again later.</td>
                  @else
                  @foreach($contacts as $contact)
                  <tr>
                      <td><a style="color: white;" href="https://www.qrz.com/db/{{ $contact->eventcallsign->call }}">{{ $contact->eventcallsign->call }}</a></td>
                      <td><a style="color: white;" href="https://www.qrz.com/db/{{ $contact->operator }}">{{ $contact->operator }}</a></td>
                      <td>{{ $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC' }}</td>
                      <td>{{ $contact->mode->mode }}</td>
                      <td><a style="color: white;" href="https://www.qrz.com/db/{{ $contact->callsign }}">{{ $contact->raw_callsign }}</a></td>
                      <td>{{ $contact->freq }}</td>
                      <td>{{ $contact->band->band }}</td>
                  </tr>
                  @endforeach
                  @endif                    
              </tbody>
          </table>          
        </div>
    
        
    </x-slot>

</x-layout>
    
