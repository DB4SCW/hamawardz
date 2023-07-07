<x-layout>
    <x-slot name="title">Logcheck</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            @if(auth()->check())
            <h1 class="text-center mb-4">Logcheck for: <a href="/event/{{ $event->slug }}/stats" style="color: white;  text-decoration: underline;">{{ $event->title }}</a></h1>
            @else
            <h1 class="text-center mb-4">Logcheck for: {{ $event->title }}</h1>
            @endif
            <form action="/logcheck/{{ $event->slug}}" method="post">
                @csrf
                <div class="form-group">
                    <label for="callsign">Your callsign:</label>
                    <input name="callsign" class="form-control" type="text" placeholder="Enter your personal callsign here..." value="{{ old('callsign') }}">
                </div>
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Check">
                </div>
            </form>
            @if($event->description != null)
            <h3 class="text-center mb-4" style="margin-top: 50px;">Event description:</h3>
            <p>{!! nl2br(e($event->description)) !!}</p>
            @endif
            @if($event->info_url != null)
            <h3 class="text-center mb-4" style="margin-top: 50px;">More information:</h3>
            <p class="text-center">Event Homepage: <a href="{{ $event->info_url }}" style="color: white; text-decoration: underline;">{{ $event->info_url }}</a></p>
            @endif
            <h3 class="text-center mb-4" style="margin-top: 50px;">Participating callsigns ({{ $event->callsigns->count() }}):</h3>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
                <thead class="thead-light">
                    <tr>
                        <th>Callsign</th>
                        <th>DXCC</th>
                        <th>Continent</th>
                        <th>ITU Zone</th>
                        <th>CQ-Zone</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->callsigns->sortby('call') as $callsign)
                    <tr>
                        <td><a href="https://www.qrz.com/db/{{$callsign->call}}" style="color: white;">{{ $callsign->call }}</a></td>
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
    
