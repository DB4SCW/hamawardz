<x-layout>
    <x-slot name="title">Login</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Login</h1>
                    <form action="/login" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Login">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

</x-layout>
    
