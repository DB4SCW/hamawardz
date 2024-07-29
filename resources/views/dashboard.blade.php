<x-layout>
    <x-slot name="title">Hamawardz - Dashboard for {{ auth()->user()->username }}</x-slot>

    <x-slot name="slot">
        <div class="container mt-5" style="margin-bottom: 60px;">
            <h1 class="text-center mb-4">Dashboard for {{ auth()->user()->username }}</h1>
            <div class="col-md-12 text-center" style="margin-bottom: 10px;">
                <a href="/upload"><button class="btn btn-warning">Upload new </button></a>
            </div>
            @if(auth()->user()->id == 1)
            <div class="col-md-12 text-center" style="margin-bottom: 10px;">
              <a href="/executeautoimport"><button class="btn btn-danger">Trigger Auto-Import</button></a>
            </div>
            @endif
            <table class="table table-bordered table-hover table-dark">
                <thead class="thead-light">
                    <tr>
                        <th>Upload ID</th>
                        <th>Uploaded At</th>
                        <th>Type</th>
                        <th>Callsign</th>
                        <th># of QSOs (Errors)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uploads as $upload)
                    <tr>
                        <td>{{ $upload->id }}</td>
                        <td>{{ $upload->created_at->format('Y-m-d @ H:i') . ' UTC' }}</td>
                        <td>{{ $upload->type }}</td>
                        <td>{{ $upload->callsign->call }}</td>
                        @if($upload->overall_qso_count - $upload->contacts->count() == 0)
                        <td>{{ $upload->contacts->count() }}</td>
                        @else
                        <td>{{ $upload->contacts->count() }} <a href="/uploads/{{$upload->id}}/showerrors" style="color: red; text-decoration: underline;">({{ $upload->overall_qso_count - $upload->contacts->count() }} Errors)</a></td>
                        @endif
                        <td>
                            <button class="btn btn-danger" onclick="showConfirmDeleteModal({{ $upload->id }})">Delete</button>
                            <a href="/uploads/{{$upload->id}}/showcontacts"><button class="btn btn-success">Show QSOs</button></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
          </div>
    
        <!-- Modal dialog to confirm deletion of upload -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                  Are you sure you want to delete this upload?
                </div>
                <div class="modal-footer">
                  <form id="confirmDeleteForm" method="post" action="/upload/delete">
                    @csrf
                    
                    <input type="hidden" id="uploadIdInputnottoken" name="uploadId" value="">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
                </div>
              </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="scripts">
        <script>
            let uploadIdToDelete; // Variable to store the upload ID to be deleted
        
            // Function to show the confirmation modal dialog
            function showConfirmDeleteModal(uploadId) {
                uploadIdToDelete = uploadId; // Store the upload ID in the variable
                $('#uploadIdInputnottoken').val(uploadId); //set id to hidden input field
                document.getElementById('message').innerHTML = "".concat('Are you sure you want to delete upload number ', uploadId, '?'); // Setup a nice message containing the upload id
                $('#confirmDeleteModal').modal('show'); // Show the modal
            }
        </script>
    </x-slot>

</x-layout>
    
