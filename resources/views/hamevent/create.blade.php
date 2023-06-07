<x-layout>
    <x-slot name="title">Create event</x-slot>

    <x-slot name="slot">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="text-center mb-4">Create event:</h1>
                    <form action="/event/create" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input name="title" class="form-control" type="text" value="{{ old('title') }}">
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug (will be auto-generated from title when empty):</label>
                            <input name="slug" class="form-control" type="text" value="{{ old('slug') }}">
                        </div>
                        <div class="form-group">
                            <label for="info_url">Info-URL:</label>
                            <input name="info_url" class="form-control" type="text" value="{{ old('info_url') }}">
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea name="description" class="form-control" style="height: 150px;">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="start">Start:</label>
                            <input name="start" class="form-control" type="datetime-local" value="{{ old('start') }}">
                        </div>
                        <div class="form-group">
                            <label for="end">End:</label>
                            <input name="end" class="form-control" type="datetime-local" value="{{ old('end') }}">
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Create">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

</x-layout>