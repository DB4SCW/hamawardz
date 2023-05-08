<x-layout>
    <x-slot name="title">Create autoimport config</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Create autoimport config:</h1>
                    <form action="/autoimport/{{ $autoimport->id }}/edit" method="post" style="margin-bottom: 60px;">
                        @csrf
                        <div class="form-group">
                            <label for="callsign_id">Event-Callsign:</label>
                            <select class="form-control" id="callsign_id" name="callsign_id">
                                @foreach($callsigns as $callsign)
                                    <option value="{{ $callsign->id }}" {{ (old('callsign_id') ?? $autoimport->callsign_id) == $callsign->id  ? 'selected' : '' }}>{{ $callsign->call }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="databasename">Database Name:</label>
                            <input name="databasename" class="form-control" type="text" value="{{ old('databasename') ?? $autoimport->databasename }}">
                        </div>
                        <div class="form-group">
                            <label for="tablename">Table name:</label>
                            <input name="tablename" class="form-control" type="text" value="{{ old('tablename') ?? $autoimport->tablename }}">
                        </div>
                        <div class="form-group">
                            <label for="table_id">Table ID field:</label>
                            <input name="table_id" class="form-control" type="text" value="{{ old('table_id') ?? $autoimport->table_id }}">
                        </div>
                        <div class="form-group">
                            <label for="operator">Operator Field (fixed value enclosed in ''):</label>
                            <input name="operator" class="form-control" type="text" value="{{ old('operator') ?? $autoimport->operator }}">
                        </div>
                        <div class="form-group">
                            <label for="qsodate">QSO Date Field (fixed value enclosed in ''):</label>
                            <input name="qsodate" class="form-control" type="text" value="{{ old('qsodate') ?? $autoimport->qsodate }}">
                        </div>
                        <div class="form-group">
                            <label for="qsotime">QSO Time Field (fixed value enclosed in ''):</label>
                            <input name="qsotime" class="form-control" type="text" value="{{ old('qsotime') ?? $autoimport->qsotime }}">
                        </div>
                        <div class="form-group">
                            <label for="qsopartner_callsign">Callsign Field (fixed value enclosed in ''):</label>
                            <input name="qsopartner_callsign" class="form-control" type="text" value="{{ old('qsopartner_callsign') ?? $autoimport->qsopartner_callsign }}">
                        </div>
                        <div class="form-group">
                            <label for="frequency">Frequency Field (fixed value enclosed in ''):</label>
                            <input name="frequency" class="form-control" type="text" value="{{ old('frequency') ?? $autoimport->frequency }}">
                        </div>
                        <div class="form-group">
                            <label for="band">Band Field (fixed value enclosed in ''):</label>
                            <input name="band" class="form-control" type="text" value="{{ old('band') ?? $autoimport->band }}">
                        </div>
                        <div class="form-group">
                            <label for="mode">Mode Field (fixed value enclosed in ''):</label>
                            <input name="mode" class="form-control" type="text" value="{{ old('mode') ?? $autoimport->mode }}">
                        </div>
                        <div class="form-group">
                            <label for="rst_s">RST-S Field (fixed value enclosed in ''):</label>
                            <input name="rst_s" class="form-control" type="text" value="{{ old('rst_s') ?? $autoimport->rst_s }}">
                        </div>
                        <div class="form-group">
                            <label for="rst_r">RST-R Field (fixed value enclosed in ''):</label>
                            <input name="rst_r" class="form-control" type="text" value="{{ old('rst_r') ?? $autoimport->rst_r }}">
                        </div>
                        <div class="form-group">
                            <label for="dxcc">DXCC-ID Field (fixed value enclosed in ''):</label>
                            <input name="dxcc" class="form-control" type="text" value="{{ old('dxcc') ?? $autoimport->dxcc }}">
                        </div>
                        <div class="form-group">
                            <label for="active">Active?</label>
                            <select class="form-control" name="active">
                                <option value="0" {{ 0 == (old('active') ?? $autoimport->active) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('active') ?? $autoimport->active) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Edit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

</x-layout>