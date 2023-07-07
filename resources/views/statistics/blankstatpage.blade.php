<x-layout>
    <x-slot name="title">Statistic Dashboard</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            @if($event->info_url != null)
            <h1 class="text-center mb-4">Statistics for: <a href="{{ $event->info_url }}" style="color: white;  text-decoration: underline;">{{ $event->title }}</a></h1>
            @else
            <h1 class="text-center mb-4">Statistics for: {{ $event->title }}</h1>
            @endif
            <p class="text-center">Event duration: {{ $event->start->format('Y-m-d') }} to {{ $event->end->format('Y-m-d') }}</p>
           
            <br>

            <div class="col-md-12 text-center" style="margin-bottom: 20px;">
                <a href="/event/{{ $event->slug }}/stats"><button class="btn btn-warning">Back</button></a>
            </div>

            <h2 class="text-center mb-4">{{ $header }}:</h2>

            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
                <thead class="thead-light">
                    <tr>
                        <th>{{ $descriptionheader }}</th>
                        <th style="text-align: right;">{{ $dataheader }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stats as $stat)
                    <tr>
                        <td>{{ array_values((array)$stat)[0] }}</td>
                        <td style="text-align: right;">{{ array_values((array)$stat)[1] }}</td>
                    </tr>
                    @endforeach
                    
                </tbody>
            </table>          


        </div>

    </x-slot>

</x-layout>
    
