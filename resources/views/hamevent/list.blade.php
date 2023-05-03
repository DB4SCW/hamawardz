<x-layout>
    <x-slot name="title">List events</x-slot>

    <x-slot name="slot">
        <div class="container admincontainer mt-5">
            <h1 class="text-center mb-4">Events you are a manager of:</h1>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
              <thead class="thead-light">
                  <tr>
                      <th>Eventname</th>
                      <th>Event-Slug</th>
                      <th>Created at</th>
                      <th>Created by</th>
                      <th>Start</th>
                      <th>End</th>
                      <th>callsigns</th>
                      <th>managers</th>
                      <th>awards</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach($events as $event)
                  <tr>
                      <td style="vertical-align: middle;">{{ $event->title }}</td>
                      <td style="vertical-align: middle;">{{ $event->slug }}</td>
                      <td style="vertical-align: middle;">{{ $event->created_at->format('Y-m-d @ H:i') . ' UTC' }}</td>
                      <td style="vertical-align: middle;">{{ $event->creator->username }}</td>
                      <td style="vertical-align: middle;">{{ $event->start->format('Y-m-d @ H:i') . ' UTC' }}</td>
                      <td style="vertical-align: middle;">{{ $event->end->format('Y-m-d @ H:i') . ' UTC' }}</td>
                      <td style="vertical-align: middle;">{{ $event->callsigns->count() }}</td>
                      <td style="vertical-align: middle;">{{ $event->eventmanagers->count() }}</td>
                      <td style="vertical-align: middle;">{{ $event->awards->count() }}</td>
                      <td>
                        <a href="/event/{{ $event->slug }}"><button class="btn btn-primary" style="margin: 5px;">Edit</button></a>
                        @if($event->callsigns->count() <= 0 || $event->awards->count() <= 0 || $event->eventmanagers->count() <= 0)
                        <a href="/event/{{ $event->slug }}/delete"><button class="btn btn-danger" style="margin: 5px;">DELETE</button></a>
                        @endif
                    </td>
                  </tr>
                  @endforeach
              </tbody>
          </table>          
        </div>
    </x-slot>

</x-layout>