<x-layout>
    <x-slot name="title">Edit Callsign-API</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Edit Callsign-API for Call {{ $api->callsign->call }}:</h1>
                    <div class="col-md-12 text-center" style="margin-bottom: 20px;">
                        <a href="/callsign/{{ $api->callsign->call }}"><button class="btn btn-warning">Back to callsign</button></a>
                    </div>
                    <div class="container mt-5">
                        <form action="/callsignapi/{{ $api->id }}/edit" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="username">Username to store the uploads in:</label>
                                <input name="username" class="form-control" type="text" value="{{ $api->contextuser->username }}">
                            </div>
                            <div class="form-group">
                                <label for="type">API Type:</label>
                                <select class="form-control" name="type">
                                    <option value="wavelog" {{ $api->type == 'wavelog' ? 'selected' : '' }}>Wavelog</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="url">API URL:</label>
                                <input name="url" class="form-control" type="text" value="{{ $api->url }}">
                            </div>
                            <div class="form-group">
                                <label for="payload">Payload for API:</label>
                                <input name="payload" class="form-control" type="text" value="{{ $api->payload }}">
                            </div>
                            <div class="form-group">
                                <label for="goalpost">Goalpost for API:</label>
                                <input name="goalpost" class="form-control" type="text" value="{{ $api->goalpost }}">
                            </div>
                            <div class="form-group">
                                <label for="active">API active?</label>
                                <select class="form-control" name="active">
                                    <option value="0" {{ $api->active ? '' : 'selected' }}>No</option>
                                    <option value="1" {{ $api->active ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="text-center">
                                <input type="submit" class="btn btn-primary" value="Edit API">
                            </div>
                        </form>   
                    </div>
                </div>
            </div>
        </div>

        
    </x-slot>

</x-layout>