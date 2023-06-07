<x-layout>
    <x-slot name="title">Logcheck</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            @if($event->info_url != null)
            <h1 class="text-center mb-4">Logcheck for: <a href="{{ $event->info_url }}" style="color: white;">{{ $event->title }}</a></h1>
            @else
            <h1 class="text-center mb-4">Logcheck for: {{ $event->title }}</h1>
            @endif
            <p class="text-center">Event duration: {{ $event->start->format('Y-m-d') }} to {{ $event->end->format('Y-m-d') }}</p>
            <h2 class="text-center mb-4">Your Callsign: {{ $callsign }}</h2>
            
            <br>

            <div class="form-group">
                <label for="chosenname" style="font-size: 20px;">Enter your name to be printed on award:</label>
                <input id="input" name="chosenname" class="form-control" type="text" placeholder="Enter your chosen name here..." oninput="copyInputValue()" value="{{ old('chosenname') }}">
            </div>

            <table class="table table-bordered table-hover table-dark">
                <thead class="thead-light">
                    <tr>
                        <th>No.</th>
                        <th>Award</th>
                        <th>Description</th>
                        <th>Info</th>
                        <th>QSOs</th>
                        <th>Eligible?</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($awards as $award)
                    <tr>
                        <td style="vertical-align: middle;">{{ $loop->iteration }}</td>
                        <td style="vertical-align: middle;">{{ $award->title }}</td>
                        <td style="vertical-align: middle;">{{ $award->description }}</td>
                        <td style="vertical-align: middle;">
                            <div class="btn-group" style="vertical-align: middle;">
                                <p id="modetext_{{ $loop->iteration }}" style="display: none;">{{ $award->mode_text() }}</p>
                                <button class="btn btn-info" style="float: right;" onclick="showhelp({{ $loop->iteration }})">?</button>
                            </div>
                        </td>
                        <td style="vertical-align: middle;">{{ $award->aggregate_count($callsign) }}</td>
                        <td style="vertical-align: middle;">{{ $award->eligible($callsign) ? 'yes' : 'no' }}</td>
                        @if($award->eligible($callsign))
                        <td style="vertical-align: middle;" class="table-action">
                            <form action="/awards/{{ $award->slug }}/pdf" method="post" style="margin-right: 5px;">
                                @csrf
                                <input type="hidden" id="callsign" name="callsign" value="{{ $callsign }}">
                                <input type="hidden" id="chosenname" name="chosenname" value="">
                                <button class="btn btn-primary" type="submit">Download</button>
                            </form>
                        </td>
                        @else
                        <td></td>
                        @endif
                    </tr>
                    @endforeach
                    
                </tbody>
            </table>

            <br>

            <h2 class="text-center mb-4">Your QSOs during this event:</h2>

            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
                <thead class="thead-light">
                    <tr>
                        <th>Eventcallsign</th>
                        <th>Operator</th>
                        <th>QSO Date and Time</th>
                        <th>Mode</th>
                        <th>Your Callsign</th>
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
                        <td>{{ $contact->eventcallsign->call }}</td>
                        <td>{{ $contact->operator }}</td>
                        <td>{{ $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC' }}</td>
                        <td>{{ $contact->mode->mode }}</td>
                        <td>{{ $contact->raw_callsign }}</td>
                        <td>{{ $contact->freq }}</td>
                        <td>{{ $contact->band->band }}</td>
                    </tr>
                    @endforeach
                    @endif                    
                </tbody>
            </table>          

        </div>

        <!-- Modal for Infotext -->
        <div class="modal fade" id="modeinfomodal" tabindex="-1" role="dialog" aria-labelledby="modeinfomodalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="modeinfomodal">Info for award:</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                    <p id="infomodaloutput"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
              </div>
            </div>
        </div>

    </x-slot>

    <x-slot name="scripts">
        <!-- Script to set chosen name for award printing and show help dialogue for each award -->
        <script>
            function copyInputValue() {
                var inputValue = document.getElementById("input").value;
                const outputelements = document.querySelectorAll("#chosenname");

                outputelements.forEach((element) => {
                    element.value = inputValue;
                });
            }

            function showhelp(awardno) {
                var modetext = document.getElementById("modetext_" + awardno).innerHTML;
                var output = document.getElementById("infomodaloutput");
                output.innerHTML = modetext;
                $('#modeinfomodal').modal('show');
            }
        </script>
        <!-- Script with defer loads after DOM is ready. Here to support award->back->award with chosen name -->
        <script defer>
            copyInputValue();
        </script>
    </x-slot>

</x-layout>
    
