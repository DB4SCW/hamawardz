<x-layout>
    <x-slot name="title">Edit User</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Edit user {{ $user->username }}:</h1>
                    <form action="/user/{{ $user->id }}" method="post" style="margin-bottom: 60px;">
                        @csrf
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input name="username" class="form-control" type="text" value="{{ old('username') ?? $user->username }}">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input name="password" class="form-control" type="password" value="{{ old('password') }}">
                        </div>
                        <div class="form-group">
                            <label for="siteadmin">Siteadmin?</label>
                            <select class="form-control" name="siteadmin">
                                <option value="0" {{ 0 == (old('siteadmin') ?? $user->siteadmin) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('siteadmin') ?? $user->siteadmin) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cancreateevents">Can create events?</label>
                            <select class="form-control" name="cancreateevents">
                                <option value="0" {{ 0 == (old('cancreateevents') ?? $user->cancreateevents) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('cancreateevents') ?? $user->cancreateevents) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="locked">Locked?</label>
                            <select class="form-control" name="locked">
                                <option value="0" {{ 0 == (old('locked') ?? $user->locked) ? 'selected' : '' }}>no</option>
                                <option value="1" {{ 1 == (old('locked') ?? $user->locked) ? 'selected' : '' }}>yes</option>
                            </select>
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Edit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

</x-layout>