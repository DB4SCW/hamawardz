<x-layout>
    <x-slot name="title">Profile for user {{ $user->username }}</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <h1 class="text-center mb-4">Profile for user {{ $user->username }}:</h1>

            <h3>Change your password:</h3>
            <form action="/profile" method="post">
                @csrf
                <div class="form-group">
                    <label for="oldpw">Old password:</label>
                    <input name="oldpw" class="form-control" type="password" value="">
                </div>
                <div class="form-group">
                    <label for="newpw1">New password:</label>
                    <input name="newpw1" class="form-control" type="password" value="">
                </div>
                <div class="form-group">
                    <label for="newpw2">Confirm new password:</label>
                    <input name="newpw2" class="form-control" type="password" value="">
                </div>
                <div class="text-center">
                    <input type="submit" class="btn btn-primary" value="Change">
                </div>
            </form>

            <br>

            <h3>You are permitted to upload QSOs for these callsigns:</h3>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
                <thead class="thead-light">
                    <tr>
                        <th>Callsign</th>
                    </tr>
                </thead>
                <tbody>
                    @if($callsigns->count() < 1)
                        <td style="text-align: center; ">You do not have any permissions to upload.</td>
                    @else
                    @foreach($callsigns as $callsign)
                    <tr>
                        <td>{{ $callsign->call }}</td>
                    </tr>
                    @endforeach
                    @endif                    
                </tbody>
            </table>         
        </div>
    
        
    </x-slot>

</x-layout>
    
