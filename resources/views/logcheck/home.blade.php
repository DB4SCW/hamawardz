<x-layout>
    <x-slot name="title">Hamawardz - Logcheck</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <h1 class="text-center mb-4">Hamawardz - Logcheck</h1>
            <form action="/logcheck" method="post">
                @csrf
                <div class="form-group">
                    <label for="eventid">Choose Event / Award to check your logs:</label>
                    <select class="form-control" id="eventid" name="eventid">
                        @foreach($events as $event)
                        <option value="{{$event->id}}" {{ $event->homepage_default ? 'selected' : '' }}>{{$event->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Check">
                </div>
            </form>
        </div>
    </x-slot>

</x-layout>
    
