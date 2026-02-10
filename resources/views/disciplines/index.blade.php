<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Discipline Selection</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Weekly Digest — Disciplines</h1>
    <p class="mt-2 text-sm text-gray-600">
      Toggle which disciplines are included. Your selection is kept in the session for now.
    </p>

    @if (session('status'))
      <div class="mt-4 rounded-md bg-green-50 border border-green-200 p-3 text-green-800">
        {{ session('status') }}
      </div>
    @endif

    <form class="mt-6" method="POST" action="{{ route('disciplines.update') }}">
      @csrf

      <div class="flex items-center justify-between mb-3">
        <div class="text-sm text-gray-700">
          <span class="font-medium">{{ $countSel }}</span> / {{ $countAll }} selected
        </div>
        <div class="space-x-2">
          <button type="button" id="selectAll"
                  class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">
            Select all
          </button>
          <button type="button" id="selectNone"
                  class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">
            Select none
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach ($all as $slug => $meta)
          @php
            $id = "disc-$slug";
            $ready = $meta['ready'] ?? false;
          @endphp
          <div class="flex items-start justify-between gap-3 p-3 rounded border bg-white hover:bg-gray-50 {{ $ready ? '' : 'opacity-50 cursor-not-allowed' }}">
            <div class="flex items-start gap-3">
              <input
                id="{{ $id }}"
                type="checkbox"
                name="disciplines[]"
                value="{{ $slug }}"
                class="mt-1 h-4 w-4"
                @checked(in_array($slug, $selected, true))
                {{ $ready ? '' : 'disabled' }}
              />
              <div>
                <label for="{{ $id }}" class="font-medium {{ $ready ? 'cursor-pointer' : 'cursor-not-allowed text-gray-500' }}">
                  {{ $meta['label'] }}
                </label>
                <div class="text-xs text-gray-500">{{ $slug }}</div>
                @unless($ready)
                  <div class="text-xs text-gray-400 mt-1">Coming soon</div>
                @endunless
              </div>
            </div>

            @if ($ready)
              <a href="{{ route('disciplines.show', $slug) }}"
                 class="shrink-0 px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">
                Details
              </a>
            @else
              <span class="shrink-0 px-3 py-1 rounded bg-gray-100 text-sm text-gray-400">Details</span>
            @endif
          </div>
        @endforeach
      </div>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        {{-- Save Selection --}}
        <button type="submit"
                class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
          Save selection
        </button>

        {{-- Generate AI Digest --}}
        <button type="submit"
                formaction="{{ route('digest.generate') }}"
                formmethod="POST"
                class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
          Generate weekly digest (AI)
        </button>

        {{-- Reset --}}
        <a href="{{ route('disciplines.index') }}"
           class="text-sm text-gray-600 underline">
          Reset (reload)
        </a>
      </div>
    </form>
  </div>

  <script>
    const selectAllBtn = document.getElementById('selectAll');
    const selectNoneBtn = document.getElementById('selectNone');

    selectAllBtn?.addEventListener('click', () => {
      document.querySelectorAll('input[name="disciplines[]"]:not([disabled])')
        .forEach(cb => cb.checked = true);
    });

    selectNoneBtn?.addEventListener('click', () => {
      document.querySelectorAll('input[name="disciplines[]"]:not([disabled])')
        .forEach(cb => cb.checked = false);
    });
  </script>
</body>
</html>
