<x-layout>
    <x-slot name="title">Logcheck</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <h1 class="text-center mb-4">Logcheck for: {{ $event->title }}</h1>
            <form action="/logcheck/{{ $event->slug}}" method="post">
                @csrf
                <div class="form-group">
                    <label for="callsign">Your callsign:</label>
                    <input name="callsign" class="form-control" type="text" value="{{ old('callsign') }}">
                </div>
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Check">
                </div>
            </form>
            @if($event->description != null)
            <h3 class="text-center mb-4" style="margin-top: 50px;">Event description:</h3>
            <p>{!! nl2br(e($event->description)) !!}</p>
            @endif
            <h3 class="text-center mb-4" style="margin-top: 50px;">participating callsigns:</h3>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
                <thead class="thead-light">
                    <tr>
                        <th>Callsign</th>
                        <th>DXCC</th>
                        <th>Continent</th>
                        <th>ITU</th>
                        <th>WAZ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->callsigns->sortby('call') as $callsign)
                    <tr>
                        <td>{{ $callsign->call }}</td>
                        <td>{{ $callsign->dxcc->name }}</td>
                        <td>{{ $callsign->dxcc->cont }}</td>
                        <td>{{ $callsign->dxcc->itu }}</td>
                        <td>{{ $callsign->dxcc->waz }}</td>
                    </tr>
                    @endforeach      
                </tbody>
            </table>
        </div>
    </x-slot>

</x-layout>
    
