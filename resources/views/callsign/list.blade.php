<x-layout>
    <x-slot name="title">List callsigns</x-slot>

    <x-slot name="slot">

        <div class="container mt-5" style="margin-bottom: 60px;">
            <h1 class="text-center mb-4">List of all callsigns:</h1>
            <button style="display: block; margin: auto; margin-bottom: 15px;" class="btn btn-primary text-center" onclick="$('#addCallsignModal').modal('show');">Add new Callsign</button>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
              <thead class="thead-light">
                  <tr>
                      <th>Eventcallsign</th>
                      <th>Responsible operator</th>
                      <th>Number of QSOs</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  @if($callsigns->count() < 1)
                      <td colspan="3" style="text-align: center; ">There are no registered callsigns yet. Add some!.</td>
                  @else
                  @foreach($callsigns as $callsign)
                  <tr>
                      <td style="vertical-align: middle; ">{{ $callsign->call }}</td>
                      <td style="vertical-align: middle; ">{{ $callsign->cert_holder_callsign }}</td>
                      <td style="vertical-align: middle; ">{{ $callsign->contacts->count() }}</td>
                      <td>
                        @if(auth()->user()->is_manager_of_callsign($callsign))
                            <a href="/callsign/{{ $callsign->call }}"><button class="btn btn-warning" style="margin: 5px;">Edit</button></a>
                            @if($callsign->contacts->count() <= 0)
                                <a href="/callsign/{{ $callsign->call }}/delete"><button class="btn btn-danger" style="margin: 5px;">DELETE</button></a>
                            @endif
                        @endif
                      </td>
                  </tr>
                  @endforeach
                  @endif                    
              </tbody>
          </table>       
        </div>

        <div class="modal fade" id="addCallsignModal" tabindex="-1" role="dialog" aria-labelledby="addCallsignModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="addCallsignModalLabel">Add new Callsign:</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                    <form action="/callsigns/create" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="call">Event-Callsign:</label>
                            <input name="call" class="form-control" type="text" value="">
                        </div>
                        <div class="form-group">
                            <label for="cert_holder_callsign">Callsign of responsible operator:</label>
                            <input name="cert_holder_callsign" class="form-control" type="text" value="">
                        </div>
                        <div class="form-group">
                            <label for="dxcc_id">DXCC of Callsign:</label>
                            <select class="form-control" id="dxcc_id" name="dxcc_id">
                                @foreach($dxccs as $dxcc)
                                <option value="{{$dxcc->id}}">{{ $dxcc->name . ' - ' . $dxcc->prefix }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                          <label for="valid_from">Validity start (UTC):</label>
                          <input name="valid_from" class="form-control" type="datetime-local">
                        </div>
                        <div class="form-group">
                          <label for="valid_to">Validity end (UTC):</label>
                          <input name="valid_to" class="form-control" type="datetime-local">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Add Callsign">
                        </div>
                      </form>   
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
        </div>

    </x-slot>

</x-layout>