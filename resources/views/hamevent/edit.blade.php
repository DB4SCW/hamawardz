<x-layout>
    <x-slot name="title">Edit event</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Edit event:</h1>
                    <form action="/event/{{ $event->slug }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input name="title" class="form-control" type="text" value="{{ old('title') ?? $event->title }}">
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug (will be auto-generated from title when empty):</label>
                            <input name="slug" class="form-control" type="text" value="{{ old('slug') ?? $event->slug }}">
                        </div>
                        <div class="form-group">
                            <label for="start">Start:</label>
                            <input name="start" class="form-control" type="datetime-local" value="{{ old('start') ?? $event->start->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="form-group">
                            <label for="end">End:</label>
                            <input name="end" class="form-control" type="datetime-local" value="{{ old('end') ?? $event->end->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Edit">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="container mt-5" style="max-width: 500px;">
            <h1 class="text-center mb-4">Eventmanagers:</h1>
            <table class="table table-bordered table-hover table-dark">
              <thead class="thead-light">
                  <tr>
                      <th>Username</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  @if($event->eventmanagers->count() < 1)
                    <td colspan="2" style="text-align: center; ">There are no eventmanagers apart from you for this event yet.<br>Feel free to add some!</td>
                  @else
                    @foreach($event->eventmanagers as $manager)
                    <tr>
                        <td style="vertical-align: middle;">{{ $manager->username }}</td>
                        <td style="text-align: center;">
                            @unless($manager->siteadmin || $manager->id == $event->creator->id || $manager->id == auth()->user()->id)
                                <a href="/event/{{ $event->slug }}/manager/{{ $manager->id }}/remove"><button class="btn btn-danger">Remove Manager permission</button></a>
                            @endunless
                        </td>
                    </tr>
                    @endforeach
                  @endif
              </tbody>
          </table>       
          <p style="color: red;">You can't remove site admins, the creator of the event or yourself.</p>  
          <button style="display: block; margin: auto;" class="btn btn-primary text-center" onclick="$('#addManagerModal').modal('show');">Add new event manager</button>
        </div>

        <div class="container mt-5" style="max-width: 500px;">
            <h1 class="text-center mb-4">Participating callsigns:</h1>
            <table class="table table-bordered table-hover table-dark">
              <thead class="thead-light">
                  <tr>
                      <th>Callsign</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  @if($event->awards->count() < 1)
                    <td colspan="2" style="text-align: center; ">There are no participating callsigns for this event yet.<br>Feel free to add some!</td>
                  @else
                    @foreach($event->callsigns as $callsign)
                    <tr>
                        <td style="vertical-align: middle;">{{ $callsign->call }}</td>
                        <td style="text-align: center;">
                            <a href="/event/{{ $event->slug }}/callsign/{{ $callsign->call }}/remove"><button class="btn btn-danger">Remove Callsign</button></a>
                        </td>
                    </tr>
                    @endforeach
                  @endif
              </tbody>
          </table>       
          <p style="color:red;">Attention: altering event callsigns does not change already created and downloaded awards!</p>
          <button style="display: block; margin: auto;" class="btn btn-primary text-center" onclick="$('#addParticipantsModal').modal('show');">Add new participating callsign</button> 
        </div>

        <div class="container mt-5" style="max-width: 800px;">
            <h1 class="text-center mb-4">Award tiers:</h1>
            <form action="/event/{{ $event->slug }}/addaward" method="post">
                @csrf
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Add Award">
                </div>
            </form> 
            <br>
            <table class="table table-bordered table-hover table-dark">
              <thead class="thead-light">
                  <tr>
                      <th>Award</th>
                      <th style="max-width: 320px;">Mode</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  @if($event->awards->count() < 1)
                    <td colspan="2" style="text-align: center; ">There are no registered awards for this event yet.<br>Feel free to create some!</td>
                  @else
                    @foreach($event->awards as $award)
                    <tr>
                        <td style="vertical-align: middle;">{{ $award->title }}</td>
                        <td style="vertical-align: middle; max-width: 320px;">{{ $award->mode_text() }}</td>
                        <td style="text-align: center;">
                            <a href="/awards/{{ $award->slug }}/edit"><button class="btn btn-warning">Edit Award Tier</button></a>
                            @if($award->issued_awards->count() <= 0)
                                <a href="/awards/{{ $award->slug }}/remove"><button class="btn btn-danger">Remove Award Tier</button></a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                  @endif
              </tbody>
          </table>   
          <p style="color:red; margin-bottom: 60px;">Attention: altering awards does not change already created and downloaded awards!</p>  
        </div>

        <!-- Modal to add managers -->
        <div class="modal fade" id="addManagerModal" tabindex="-1" role="dialog" aria-labelledby="addManagerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="addCallsignModalLabel">Add new event manager:</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                    <form action="/event/{{ $event->slug }}/addmanager" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input name="username" class="form-control" type="text" value="{{ old('username') }}">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Add Manager">
                        </div>
                      </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
        </div>

        <!-- Modal to add participating callsigns -->
        <div class="modal fade" id="addParticipantsModal" tabindex="-1" role="dialog" aria-labelledby="addParticipantsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="addCallsignModalLabel">Add new event manager:</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                    <form action="/event/{{ $event->slug }}/addcallsign" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="title">Callsign:</label>
                            <input name="callsign" class="form-control" type="text" value="{{ old('callsign') }}">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Add Manager">
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