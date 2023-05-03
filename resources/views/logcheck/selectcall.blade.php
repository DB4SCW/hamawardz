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
            <h3 class="text-center mb-4" style="margin-top: 50px;">participating callsigns:</h3>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
                <thead class="thead-light">
                    <tr>
                        <th>Callsign</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->callsigns->sortby('call') as $callsign)
                    <tr>
                        <td>{{ $callsign->call }}</td>
                    </tr>
                    @endforeach      
                </tbody>
            </table>
        </div>
    </x-slot>

</x-layout>
    
