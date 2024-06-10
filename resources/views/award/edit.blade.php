<x-layout>
    <x-slot name="title">Edit award</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Edit award for<br><a href="/event/{{ $award->event->slug }}" style="color: white;">{{ $award->event->title }}</a>:</h1>
                    <h4 class="text-center mb-4">{{ $award->title }}</h4>
                    <form action="/awards/{{ $award->slug }}/edit" method="post">
                        @csrf
                        <!-- Basic event data -->
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input name="title" class="form-control" type="text" value="{{ old('title') ?? $award->title }}">
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug (will be auto-generated from title when empty):</label>
                            <input name="slug" class="form-control" type="text" value="{{ old('slug') ?? $award->slug }}">
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <input name="description" class="form-control" type="text" value="{{ old('description') ?? $award->description }}">
                        </div>
                        <div class="form-group">
                            <label for="ranking">Ranking (order of awards in UI):</label>
                            <input name="ranking" class="form-control" type="number" min="0" max="100" step="1" value="{{ old('ranking') ?? $award->ranking }}">
                        </div>
                        <div class="form-group">
                            <label for="mode">Award mode</label>
                            <select class="form-control" name="mode">
                                @foreach(range(0, swolf_getmaxmode()) as $x)
                                <option value="{{ $x }}" {{ $x == (old('mode') ?? $award->mode) ? 'selected' : '' }}>{{ swolf_getawardmodetext($x, null) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="min_threshold">Threshold for mode:</label>
                            <input name="min_threshold" class="form-control" type="number" min="0" step="1" value="{{ old('min_threshold') ?? $award->min_threshold }}">
                        </div>

                        <div class="form-group">
                            <label for="excluded_callsigns">Exclude these callsigns from award computation:</label>
                            <input name="excluded_callsigns" class="form-control" type="text" value="{{ old('excluded_callsigns') ?? $award->excluded_callsigns }}">
                        </div>

                        <!-- DXCC modes handling -->
                        <div class="form-group">
                            <label for="dxcc_id">DXCC (only for DXCC modes):</label>
                            <select class="form-control" name="dxcc_id">
                                <option value="" selected>not set</option>
                                @foreach($dxccs as $dxcc)
                                <option value="{{ $dxcc->id }}" {{ old('dxcc_id') ?? $award->dxcc_id == null ? '' : ( $dxcc->id == (old('dxcc_id') ?? $award->dxcc_id) ? 'selected' : '' ) }}>{{ $dxcc->name . ' - ' . $dxcc->prefix }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="dxcc_querystring">Querystring (only for Continent or Zone query-modes):</label>
                            <input name="dxcc_querystring" class="form-control" type="text" value="{{ old('dxcc_querystring') ?? $award->dxcc_querystring }}">
                        </div>

                        <!-- Callsign formatting -->
                        <div class="form-group">
                            <label for="callsign_top_percent">Position of callsign - Top %:</label>
                            <input name="callsign_top_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('callsign_top_percent') ?? $award->callsign_top_percent }}">
                        </div>
                        <div class="form-group">
                            <label for="callsign_bold">Callsign Bold:</label>
                            <select class="form-control" name="callsign_bold">
                                <option value="0" {{ 0 == (old('callsign_bold') ?? $award->callsign_bold) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('callsign_bold') ?? $award->callsign_bold) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="callsign_font_size_px">Callsign font size:</label>
                            <input name="callsign_font_size_px" class="form-control" type="number" min="0" max="300" step="1" value="{{ old('callsign_font_size_px') ?? $award->callsign_font_size_px }}">
                        </div>

                        <!-- Chosen name formatting -->
                        <div class="form-group">
                            <label for="chosen_name_top_percent">Chosen name - Top %:</label>
                            <input name="chosen_name_top_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('chosen_name_top_percent') ?? $award->chosen_name_top_percent }}">
                        </div>
                        <div class="form-group">
                            <label for="chosen_name_bold">Chosen name Bold:</label>
                            <select class="form-control" name="chosen_name_bold">
                                <option value="0" {{ 0 == (old('chosen_name_bold') ?? $award->chosen_name_bold) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('chosen_name_bold') ?? $award->chosen_name_bold) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="chosen_name_font_size_px">Chosen name font size:</label>
                            <input name="chosen_name_font_size_px" class="form-control" type="number" min="0" max="300" step="1" value="{{ old('chosen_name_font_size_px') ?? $award->chosen_name_font_size_px }}">
                        </div>

                        <!-- datetime formatting -->
                        <div class="form-group">
                            <label for="datetime_print">print datetime?:</label>
                            <select class="form-control" name="datetime_print">
                                <option value="0" {{ 0 == (old('datetime_print') ?? $award->datetime_print) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('datetime_print') ?? $award->datetime_print) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="datetime_top_percent">Datetime - Top %:</label>
                            <input name="datetime_top_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('datetime_top_percent') ?? $award->datetime_top_percent }}">
                        </div>
                        <div class="form-group">
                            <label for="datetime_left_percent">Datetime - Left %:</label>
                            <input name="datetime_left_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('datetime_left_percent') ?? $award->datetime_left_percent }}">
                        </div>
                        <div class="form-group">
                            <label for="datetime_font_size_px">Datetime font size:</label>
                            <input name="datetime_font_size_px" class="form-control" type="number" min="0" max="300" step="1" value="{{ old('datetime_font_size_px') ?? $award->datetime_font_size_px }}">
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="active">Active?</label>
                            <select class="form-control" name="active">
                                <option value="0" {{ 0 == (old('active') ?? $award->active) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('active') ?? $award->active) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>

                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Edit">
                        </div>
                    </form>
                    <br>
                    <!-- only for award mode 9 -->
                    @if($award->mode == 9)
                    <h3>Award subtimeframes:</h3>
                    <table class="table table-bordered table-hover table-dark" style="margin-bottom: 20px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Start (UTC)</th>
                                <th>End (UTC)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($award->awardtimeframes as $timeframe)
                            <tr>
                                <td style="vertical-align: middle;">{{ $timeframe->start->format('Y-m-d @ H:i') . ' UTC' }}</td>
                                <td style="vertical-align: middle;">{{ $timeframe->end->format('Y-m-d @ H:i') . ' UTC' }}</td>
                                <td>
                                    <a href="/awardtimeframe/{{ $timeframe->id }}/delete"><button class="btn btn-danger" style="margin: 5px;">Delete</button></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>     
                    <button style="display: block; margin: auto;" class="btn btn-warning text-center" onclick="$('#addAwardSubTimeframe').modal('show');">Add new award subtimeframe</button>     
                    <br>
                    @endif
                    <h3>Background image:</h3>
                    <form action="/awards/{{ $award->slug }}/uploadbackground" method="post"enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <input type="file" class="form-control" id="file" name="file">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-warning" value="Upload">
                        </div>
                    </form>
                    <br>
                    <div class="row justify-content-center" style="margin-bottom: 60px;">
                        <a href="/awards/{{ $award->slug }}/exampleaward" style="margin-bottom: 60px;"><button class="btn btn-danger">Print example award</button></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for  adding subtimeframe only for mode 9-->
        @if($award->mode == 9)
        <div class="modal fade" id="addAwardSubTimeframe" tabindex="-1" role="dialog" aria-labelledby="addAwardSubTimeframeLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="addCallsignModalLabel">Add new award subtimeframe:</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                    <form action="/award/{{ $award->id }}/addsubtimeframe" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="start">Start (UTC):</label>
                            <input name="start" class="form-control" type="datetime-local">
                        </div>
                        <div class="form-group">
                            <label for="end">End (UTC):</label>
                            <input name="end" class="form-control" type="datetime-local">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Add Timeframe">
                        </div>
                      </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
        </div>
        @endif

    </x-slot>

</x-layout>
    

