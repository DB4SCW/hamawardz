<x-layout>
    <x-slot name="title">Edit callsign</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Edit Callsign:</h1>
                    <div class="container mt-5">
                        <form action="/callsign/{{ $callsign->call }}" method="post">
                          @csrf
                          <div class="form-group">
                              <label for="call">Event-Callsign:</label>
                              <input name="call" class="form-control" type="text" value="{{ old('call') ?? $callsign->call }}">
                          </div>
                          <div class="form-group">
                              <label for="cert_holder_callsign">Callsign of responsible operator:</label>
                              <input name="cert_holder_callsign" class="form-control" type="text" value="{{ old('cert_holder_callsign') ?? $callsign->cert_holder_callsign }}">
                          </div>
                          <div class="text-center">
                              <input type="submit" class="btn btn-primary" value="Add Callsign">
                          </div>
                        </form>   
                    </div>

                    <div class="container mt-5" style="margin-bottom: 60px;">
                        <h1 class="text-center mb-4">Users that are allowed to upload:</h1>
                        <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($callsign->uploadusers->count() < 1)
                                    <td colspan="2" style="text-align: center; ">There are no registered uploaders yet. Add some!.</td>
                                @else
                                @foreach($callsign->uploadusers as $uploader)
                                <tr>
                                    <td style="vertical-align: middle; ">{{ $uploader->username }}</td>
                                    <td>
                                    @if(auth()->user()->siteadmin || auth()->user()->id == $callsign->creator_id)
                                        @if(!$uploader->siteadmin && $uploader->id != auth()->user()->id)
                                            <a href="/callsign/{{ $callsign->call }}/user/{{ $uploader->id }}/delete"><button class="btn btn-danger" style="margin: 5px;">DELETE</button></a>
                                        @endif
                                    @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endif                    
                            </tbody>
                        </table>    
                        <button style="display: block; margin: auto;" class="btn btn-primary text-center" onclick="$('#addUploaduserModal').modal('show');">Add new upload user</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addUploaduserModal" tabindex="-1" role="dialog" aria-labelledby="addUploaduserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="addCallsignModalLabel">Add new upload user:</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                    <form action="/callsign/{{ $callsign->call }}/adduploader" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input name="username" class="form-control" type="text" value="{{ old('username') }}">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Add Uploader">
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