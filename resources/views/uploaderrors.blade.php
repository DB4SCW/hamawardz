<x-layout>
    <x-slot name="title">Errors for Upload {{ $upload->id }}</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <h1 class="text-center mb-4">Errors for Upload {{ $upload->id }}:</h1>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
              <thead class="thead-light">
                  <tr>
                      <th>Error</th>
                  </tr>
              </thead>
              <tbody>
                  @if(count($errors) < 1)
                      <td style="text-align: center; ">There are no errors for this upload.</td>
                  @else
                  @foreach($errors as $error)
                  <tr>
                      <td>{{ $error }}</td>
                  </tr>
                  @endforeach
                  @endif                    
              </tbody>
          </table>          
        </div>
    
        
    </x-slot>

</x-layout>
    
