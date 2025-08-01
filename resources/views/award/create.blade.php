<x-layout>
    <x-slot name="title">Create award</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Create award for:</h1>
                    <h4 class="text-center mb-4">{{ $event->title }}</h4>
                    <form action="/event/{{ $event->slug }}/createaward" method="post" style="margin-bottom: 60px;">
                        @csrf
                        <!-- Basic event data -->
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input name="title" class="form-control" type="text" value="{{ old('title') }}">
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug (will be auto-generated from title when empty):</label>
                            <input name="slug" class="form-control" type="text" value="{{ old('slug') }}">
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <input name="description" class="form-control" type="text" value="{{ old('description') }}">
                        </div>
                        <div class="form-group">
                            <label for="ranking">Ranking (order of awards in UI):</label>
                            <input name="ranking" class="form-control" type="number" min="0" max="100" step="1" value="{{ old('ranking') ?? 0 }}">
                        </div>
                        <div class="form-group">
                            <label for="mode">Award mode</label>
                            <select class="form-control" name="mode">
                                @foreach(range(0, db4scw_getmaxmode()) as $x)
                                <option value="{{ $x }}" {{ $x == old('mode') ? 'selected' : '' }}>{{ db4scw_getawardmodetext($x, null) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="min_threshold">Threshold for mode:</label>
                            <input name="min_threshold" class="form-control" type="number" min="0" step="1" value="{{ old('min_threshold') }}">
                        </div>

                        <div class="form-group">
                            <label for="excluded_callsigns">Exclude these callsigns from award computation:</label>
                            <input name="excluded_callsigns" class="form-control" type="text" value="{{ old('excluded_callsigns')}}">
                        </div>

                        <!-- DXCC modes handling -->
                        <div class="form-group">
                            <label for="dxcc_id">DXCC (only for DXCC modes):</label>
                            <select class="form-control" name="dxcc_id">
                                <option value="" selected>not set</option>
                                @foreach($dxccs as $dxcc)
                                <option value="{{ $dxcc->id }}" {{ old('dxcc_id') == null ? '' : ( $dxcc->id == old('dxcc_id') ? 'selected' : '' ) }}>{{ $dxcc->name . ' - ' . $dxcc->prefix }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="dxcc_querystring">Querystring (only for Continent or Zone query-modes):</label>
                            <input name="dxcc_querystring" class="form-control" type="text" value="{{ old('dxcc_querystring') }}">
                        </div>

                        <!-- Callsign formatting -->
                        <div class="form-group">
                            <label for="callsign_top_percent">Position of callsign - Top %:</label>
                            <input name="callsign_top_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('callsign_top_percent') }}">
                        </div>
                        <div class="form-group">
                            <label for="callsign_bold">Callsign Bold:</label>
                            <select class="form-control" name="callsign_bold">
                                <option value="0" {{ 0 == old('callsign_bold') ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == old('callsign_bold') ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="callsign_font_size_px">Callsign font size:</label>
                            <input name="callsign_font_size_px" class="form-control" type="number" min="0" max="300" step="1" value="{{ old('callsign_font_size_px') }}">
                        </div>
                        <div class="form-group">
                            <label for="callsign_centered_horizontal">Callsign centered horizontal? Keeping "yes" is STRONGLY advised.</label>
                            <select class="form-control" name="callsign_centered_horizontal">
                                <option value="0" {{ 0 == old('callsign_centered_horizontal') ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('callsign_centered_horizontal') ?? 1) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="callsign_left_percent">If not centered - Callsign left %:</label>
                            <input name="callsign_left_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('callsign_left_percent') }}">
                        </div>
                        <div class="form-group">
                            <label for="callsign_text_color">Callsign text color:</label>
                            <select class="form-control" name="callsign_text_color">
                                @foreach ($text_colors as $color)
                                <option value="{{ $color }}" {{ $color == old('callsign_text_color') ? 'selected' : '' }}>{{ $color }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Chosen name formatting -->
                        <div class="form-group">
                            <label for="chosen_name_top_percent">Chosen name - Top %:</label>
                            <input name="chosen_name_top_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('chosen_name_top_percent') }}">
                        </div>
                        <div class="form-group">
                            <label for="chosen_name_bold">Chosen name Bold:</label>
                            <select class="form-control" name="chosen_name_bold">
                                <option value="0" {{ 0 == old('chosen_name_bold') ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == old('chosen_name_bold') ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="chosen_name_font_size_px">Chosen name font size:</label>
                            <input name="chosen_name_font_size_px" class="form-control" type="number" min="0" max="300" step="1" value="{{ old('chosen_name_font_size_px') }}">
                        </div>
                        <div class="form-group">
                            <label for="chosen_name_centered_horizontal">Chosen name centered horizontal? Keeping "yes" is STRONGLY advised.</label>
                            <select class="form-control" name="chosen_name_centered_horizontal">
                                <option value="0" {{ 0 == old('chosen_name_centered_horizontal') ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('chosen_name_centered_horizontal') ?? 1) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="chosen_name_left_percent">If not centered - Chosen name left %:</label>
                            <input name="chosen_name_left_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('chosen_name_left_percent') }}">
                        </div>
                        <div class="form-group">
                            <label for="chosen_name_text_color">Chosen name text color:</label>
                            <select class="form-control" name="chosen_name_text_color">
                                @foreach ($text_colors as $color)
                                <option value="{{ $color }}" {{ $color == old('chosen_name_text_color') ? 'selected' : '' }}>{{ $color }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- datetime formatting -->
                        <div class="form-group">
                            <label for="datetime_print">print datetime?:</label>
                            <select class="form-control" name="datetime_print">
                                <option value="0" {{ 0 == old('datetime_print') ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == old('datetime_print') ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="datetime_top_percent">Datetime - Top %:</label>
                            <input name="datetime_top_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('datetime_top_percent') }}">
                        </div>
                        <div class="form-group">
                            <label for="datetime_left_percent">Datetime - Left %:</label>
                            <input name="datetime_left_percent" class="form-control" type="number" min="0" max="100" step="0.01" value="{{ old('datetime_left_percent') }}">
                        </div>
                        <div class="form-group">
                            <label for="datetime_font_size_px">Datetime font size:</label>
                            <input name="datetime_font_size_px" class="form-control" type="number" min="0" max="300" step="1" value="{{ old('datetime_font_size_px') }}">
                        </div>
                        <div class="form-group">
                            <label for="datetime_text_color">Datetime text color:</label>
                            <select class="form-control" name="datetime_text_color">
                                @foreach ($text_colors as $color)
                                <option value="{{ $color }}" {{ $color == old('datetime_text_color') ? 'selected' : '' }}>{{ $color }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="active">Active?</label>
                            <select class="form-control" name="active">
                                <option value="0" {{ 0 == old('active') ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == old('active') ?? 1 ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>

                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Create">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

</x-layout>
    

