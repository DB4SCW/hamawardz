<!DOCTYPE html>
<html>
    <head>
        <title>{{ $title ?? '' }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">      
        <!-- Include Bootstrap CSS file -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

        <!-- Include jQuery library -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Include Bootstrap JavaScript library -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <style>
            /* Custom CSS for dark mode */
            body {
                background-color: #343a40;
                color: #f8f9fa;
            }
            .container {
                max-width: 1080px;
            }
            .admincontainer {
                max-width: 2000px;
            }
            .table {
                background-color: #555e66; 
            }
            .btn-primary {
                background-color: rgb(59, 109, 74); /* #007bff vorher */
                border-color:rgb(59, 109, 74);
            }
            .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open>.dropdown-toggle.btn-primary {
                color: #fff;
                background-color: rgb(59, 109, 74);
                border-color:rgb(59, 109, 74);
            }
            .modal-content {
                background-color: #333;
                color: #fff;
            }
            .modal-header {
                background-color: #212529;
                color: #fff;
            }
            .modal-footer {
                background-color: #212529;
                color: #fff;
            }
            .custom-navbar {
                background-color: rgb(59, 109, 74);
            }
            .navbar-element {
                display: flex; 
                justify-content: flex-end;
            }
            .navbar-link {
                color: rgba(255, 255, 255, 0.5);
            }
            .bottom-right-alert {
                position: fixed;
                bottom: 40px;
                right: 20px;
                z-index: 9999;
            }
            .footer {
                position: fixed;
                left: 0;
                bottom: 0;
                width: 100%;
                background-color: rgb(59, 109, 74);
                padding: 10px;
                text-align: center;
                display: flex; 
                justify-content: center;
            }
            .footerelement {
                margin-left: 10px;
                color: white;
            }
        </style>
        <!-- On demand CSS -->
        {{ $styles ?? '' }}
    </head>
    <body>
         <!-- Floating Navbar -->
        <nav class="navbar fixed-top navbar-dark custom-navbar"  style="display: flex;">
            <a class="navbar-brand" href="/">Hamawardz</a>
            
            <div class="navbar-element">
                @if(auth()->check())
                    @if(auth()->user()->events_to_manage->count() > 0 || auth()->user()->siteadmin)
                        <a class="nav-link navbar-link" href="/events">Events-Admin</a>
                    @endif
                    
                    @if(auth()->user()->cancreateevents || auth()->user()->siteadmin)
                    <a class="nav-link navbar-link" href="/events/create">Create Event</a>
                    @endif

                    @if(auth()->user()->siteadmin)
                    <a class="nav-link navbar-link" href="/users">User-Admin</a>
                    <a class="nav-link navbar-link" href="/autoimports">Autoimport-Admin</a>
                    @endif

                    @if(auth()->user()->events_to_manage->count() > 0 || auth()->user()->siteadmin)
                    <a class="nav-link navbar-link" href="/callsigns">Event-Callsigns</a>
                    @endif
                    
                    <a class="nav-link navbar-link" href="/profile">{{ auth()->user()->username }} Profile</a>
                    <a class="nav-link navbar-link" href="/dashboard">Log-Upload</a>
                    <a class="nav-link navbar-link" href="/logout">Logout</a>
                @else
                    <a class="nav-link navbar-link" href="/login">Login</a>
                @endif
            </div>
        </nav>
        <br>
        
        <!-- Alerts -->
        @if(session()->has('danger'))
        <div class="bottom-right-alert">
            <div class="alert alert-danger">
                {{ session('danger') }}
            </div>
        </div>
        @endif
        @if(session()->has('warning'))
        <div class="bottom-right-alert">
            <div class="alert alert-warning">
                {{ session('warning') }}
            </div>
        </div>
        @endif
        @if(session()->has('success'))
        <div class="bottom-right-alert">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
        @endif
        @if(session()->has('light'))
        <div class="bottom-right-alert">
            <div class="alert alert-light">
                {{ session('light') }}
            </div>
        </div>
        @endif 
        
        <!-- Main Content -->
        {{ $slot }}

        <!-- Floating Footer -->
        <div class="footer">
            <a class="footerelement" href="{{ env('APP_IMPRESSUM_URL', '/') }}">Impressum</a>
            <a class="footerelement" href="{{ env('APP_DATA_PROTECTION_URL', '/') }}">Data protection declaration</a>
        </div>

        <!-- On demand JS -->
        {{ $scripts ?? '' }}

        <!-- Alert-JS -->
        <script type="text/javascript">
            window.setTimeout(function() {
                $(".alert").fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove();
                });
            }, 5000);
        </script>

    </body>
</html>