<x-layout>
    <x-slot name="title">Hamawardz - New Upload</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">New Upload:</h1>
                    <form action="/upload" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="callsignid">Event-Callsign:</label>
                            <select class="form-control" id="callsignid" name="callsignid">
                                @foreach($callsigns as $callsign)
                                    <option value="{{ $callsign->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $callsign->call }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="operator">Operator:</label>
                            <input name="operator" class="form-control" type="text" value="{{ old('operator') }}">
                        </div>
                        <div class="form-group">
                            <label for="ignoreduplicates">Ignore duplicate error messages?</label>
                            <select class="form-control" name="ignoreduplicates">
                                <option value="0" selected>no</option>
                                <option value="1">yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="file">ADIF File:</label>
                            <input type="file" class="form-control" id="file" name="file">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Upload">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

</x-layout>
    

