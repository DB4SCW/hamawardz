<x-layout>
    <x-slot name="title">List Autoimports</x-slot>

    <x-slot name="slot">
        <div class="container admincontainer mt-5">
            <h1 class="text-center mb-4">Registered autoimport configurations</h1>
            <a href="/autoimports/create"><button style="display: block; margin: auto; margin-bottom: 15px;" class="btn btn-primary text-center">Add new autoconfig</button></a>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
              <thead class="thead-light">
                  <tr>
                      <th>Callsign</th>
                      <th>Active?</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach($autoimports as $autoimport)
                  <tr>
                      <td style="vertical-align: middle;">{{ $autoimport->callsign->call }}</td>
                      <td style="vertical-align: middle;">{{ $autoimport->active ? 'yes' : 'no' }}</td>
                      <td style="text-align: center;">
                        <a href="/autoimport/{{ $autoimport->id }}/edit"><button class="btn btn-warning">Edit</button></a>
                        <a href="/autoimport/{{ $autoimport->id }}/toggle"><button class="btn {{ $autoimport->active ? 'btn-danger' : 'btn-primary' }}">Toggle</button></a>
                        <a href="/autoimport/{{ $autoimport->id }}/delete"><button class="btn btn-danger">Delete</button></a>
                    </td>
                  </tr>
                  @endforeach
              </tbody>
          </table>          
        </div>
    </x-slot>

</x-layout>