<!DOCTYPE html>
<html>
<head>
    <title>Your Award</title>
    <style>
        /* Container for the image and text overlay */
        .container {
            position: relative;
            text-align: center;           
        }

        body {
            width: 29.7cm;
  		    height: 21cm;  
            margin-left: auto;
            margin-right: auto;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* cheat on MPDF for horizontally centered div */
        .horicentre { 
            width: 100%; 
            margin: 0 auto; 
            text-align: center; 
        }

        /* The image */
        .image {
            width: 100%;
            height: 100%;
            margin: 0 auto;
        }

        /* The text overlays, configured by the database */
        .callsign {
            position: absolute;
            top: {{ $award->callsign_top_percent }}%;
            @if(!$award->callsign_centered_horizontal)
            left: {{ $award->callsign_left_percent ?? 50 }}%;
            @endif
            text-align: center;
            font-size: {{ $award->callsign_font_size_px }}px;
            color: {{ $award->callsign_text_color }};
            @if($award->callsign_bold)
            font-weight: bold;
            @endif
        }

        .chosenname {
            position: absolute;
            top: {{ $award->chosen_name_top_percent }}%;
            @if(!$award->chosen_name_centered_horizontal)
            left: {{ $award->chosen_name_left_percent ?? 50 }}%;
            @endif
            text-align: center;
            font-size: {{ $award->chosen_name_font_size_px }}px;
            color: {{ $award->chosen_name_text_color }};
            @if($award->chosen_name_bold)
            font-weight: bold;
            @endif
        }

        .datetime {
            position: absolute;
            top: {{ $award->datetime_top_percent }}%;
            left: {{ $award->datetime_left_percent }}%;
            text-align: center;
            font-size: {{ $award->datetime_font_size_px }}px;
            color: {{ $award->datetime_text_color }};
        }

    </style>
</head>
<body>
    <!-- The background image -->
    <div class="container">
        <img class="image" src="{{ $award->backgroundimage_assetpath() }}" alt="Award Background">
    </div>
    <!-- The overlays -->
    <div class="callsign {{ $award->callsign_centered_horizontal ? 'horicentre' : '' }}">{{ $callsign }}</div>
    <div class="chosenname {{ $award->chosen_name_centered_horizontal ? 'horicentre' : '' }}">{{ $chosenname ?? '' }}</div>
    @if($award->datetime_print)
    <div class="datetime">{{ $issue_datetime->format('Y-m-d @ H:i') . ' UTC' }}</div>
    @endif
</body>
</html>
