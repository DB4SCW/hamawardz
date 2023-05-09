<x-layout>
    <x-slot name="title">List users</x-slot>

    <x-slot name="slot">
        <div class="container admincontainer mt-5">
            <h1 class="text-center mb-4">Registered users</h1>
            <button style="display: block; margin: auto; margin-bottom: 15px;" class="btn btn-primary text-center" onclick="$('#addUserModal').modal('show');">Add new user</button>
            <table class="table table-bordered table-hover table-dark" style="margin-bottom: 60px;">
              <thead class="thead-light">
                  <tr>
                      <th>Username</th>
                      <th>Locked?</th>
                      <th>Site-Admin</th>
                      <th>Can create events</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach($users as $user)
                  <tr>
                      <td style="vertical-align: middle;">{{ $user->username }}</td>
                      <td style="vertical-align: middle;">{{ $user->locked ? 'yes' : 'no' }}</td>
                      <td style="vertical-align: middle;">{{ $user->siteadmin ? 'yes' : 'no' }}</td>
                      <td style="vertical-align: middle;">{{ $user->cancreateevents ? 'yes' : 'no' }}</td>
                      <td style="vertical-align: middle;">
                        <a href="/users/{{ $user->id }}/toggle"><button class="btn {{ $user->locked ? 'btn-primary' : 'btn-danger' }}">Toggle Lock</button></a>
                      </td>
                  </tr>
                  @endforeach
              </tbody>
          </table>          
        </div>

        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
              <div class="modal-content">
                <div class="modal-header modal-dark">
                  <h5 class="modal-title" id="addUserModalLabel">Add new user:</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="message">
                    <form action="/users/create" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input name="username" class="form-control" type="text" value="{{ old('username') }}">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input name="password" class="form-control" type="password" value="">
                        </div>
                        <div class="form-group">
                            <label for="siteadmin">Site-Admin:</label>
                            <select class="form-control" name="siteadmin">
                                <option value="0" selected>no</option>
                                <option value="1">yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cancreateevents">Can create events:</label>
                            <select class="form-control" name="cancreateevents">
                                <option value="0" selected>no</option>
                                <option value="1">yes</option>
                            </select>
                        </div>
                        <div class="text-center">
                            <input type="submit" class="btn btn-primary" value="Add User">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
        </div>
    </x-slot>

</x-layout>