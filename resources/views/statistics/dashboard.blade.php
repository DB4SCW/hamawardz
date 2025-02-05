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
            <p class="text-center">Database: {{ $databasetype }}</p>
           
            <br>

            <h2 class="text-center mb-4">Available statistics:</h2>

            <table class="table table-bordered table-hover table-dark">
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Partipant QSO Leaderboard</td>
                        <td><a href="/event/{{ $event->slug }}/stat/qso_leaderboard"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>
                    <tr>
                        <td>Event callsign QSO Leaderboard</td>
                        <td><a href="/event/{{ $event->slug }}/stat/callsign_leaderboard"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>
                    <tr>
                        <td>Event operator QSO Leaderboard</td>
                        <td><a href="/event/{{ $event->slug }}/stat/operator_leaderboard"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>
                    <tr>
                        <td>Partipant DXCC Leaderboard (if unknow data is present, click "Fix DXCCs")</td>
                        <td><a href="/event/{{ $event->slug }}/stat/dxcc_leaderboard"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>
                    <tr>
                        <td>Partipant Continent Leaderboard (if unknow data is present, click "Fix DXCCs")</td>
                        <td><a href="/event/{{ $event->slug }}/stat/continent_leaderboard"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>
                    <tr>
                        <td>Band Leaderboard</td>
                        <td><a href="/event/{{ $event->slug }}/stat/band_leaderboard"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>
                    <tr>
                        <td>Mode Leaderboard</td>
                        <td><a href="/event/{{ $event->slug }}/stat/mode_leaderboard"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>
                    <tr>
                        <td>Event callsigns last ADIF upload</td>
                        <td><a href="/event/{{ $event->slug }}/stat/lastuploads"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>    
                    <tr>
                        <td>List created awards</td>
                        <td><a href="/event/{{ $event->slug }}/stat/createdawards"><button class="btn btn-primary" style="margin: 5px;">Show</button></a></td>
                    </tr>              
                </tbody>
            </table>          

            <div class="col-md-12 text-center" style="margin-bottom: 60px;">
                <a href="/runtask/fixdxccs"><button class="btn btn-primary">Fix DXCCs</button></a>
                <p style="color: red;">May take a while, depending on your data... Uses {{ db4scw_determine_dxcc_api_mode() }} API.</p>
            </div>
            
            <br>

        </div>

    </x-slot>

</x-layout>
    
