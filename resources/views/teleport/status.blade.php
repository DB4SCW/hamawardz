<x-layout>
    <x-slot name="styles">
        <meta http-equiv="refresh" content="3">
        <style>
            .import-container {
                margin-top: 160px;
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
                text-align: center;
                padding: 60px;
                background-color: rgba(255,255,255,0.05);
                border-radius: 12px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }

            .progress {
                height: 25px;
                background-color: #e9ecef;
                border-radius: 12px;
                overflow: hidden;
            }

            .progress-bar {
                transition: width 0.5s ease;
            }

            h1 {
                margin-bottom: 20px;
            }
            h2 {
                margin-bottom: 20px;
            }

            .status-text {
                margin-top: 15px;
                font-size: 1.2em;
            }
        </style>
    </x-slot>

    <x-slot name="slot">
        <div class="import-container" >
            <h1>🚀</h1>
            <h1>Teleport in progress</h1>
            <h2>Beam my up, Scotty...</h2>

            <div class="progress mb-3">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $percent }}%
                </div>
            </div>

            <div class="status-text">
                {{ $status }}
            </div>

            <p class="mt-4 text-muted">Diese Seite aktualisiert sich automatisch.</p>
        </div>
    </x-slot>
</x-layout>
