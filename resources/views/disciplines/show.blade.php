<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $label }} — Sources</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('disciplines.index') }}" class="text-sm underline text-gray-600">← Back</a>
    <h1 class="mt-2 text-2xl font-bold">{{ $label }} — Sources</h1>
    <p class="text-sm text-gray-600">
      Choose which sources feed your weekly digest for <strong>{{ $label }}</strong>.
    </p>

    @if (session('status'))
      <div class="mt-4 rounded bg-green-50 border border-green-200 p-3 text-green-800">
        {{ session('status') }}
      </div>
    @endif

    <form class="mt-6" method="POST" action="{{ route('disciplines.sources.update', $slug) }}">
      @csrf

      <div class="flex items-center justify-between mb-3">
        <div class="text-sm text-gray-700">
          <span class="font-medium">{{ count($selected) }}</span> selected
        </div>
        <div class="space-x-2">
          <button type="button" id="selectAll" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">Select all</button>
          <button type="button" id="selectNone" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">Select none</button>
        </div>
      </div>

      <div class="space-y-3">
        @foreach ($sources as $s)
          @php $id = 'src-'.$s['key']; @endphp
          <div class="p-3 rounded-lg border bg-white hover:shadow-sm transition">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <input
                  id="{{ $id }}"
                  type="checkbox"
                  name="sources[]"
                  value="{{ $s['key'] }}"
                  class="mt-1 h-4 w-4"
                  @checked(in_array($s['key'], $selected, true))
                />
                <div>
                  <label for="{{ $id }}" class="font-semibold cursor-pointer">{{ $s['label'] }}</label>
                  <div class="text-xs text-gray-500">
                    {{ $s['kind'] ?? 'source' }} • {{ $s['signal'] ?? '—' }}
                  </div>
                  @if(!empty($s['notes']))
                    <div class="text-xs text-gray-600 mt-1">{{ $s['notes'] }}</div>
                  @endif
                </div>
              </div>
              <div class="shrink-0">
                <a href="{{ route('sources.preview', [$slug, $s['key']]) }}" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">
                  Preview
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
          Save sources
        </button>

        {{-- Generate an AI digest for THIS discipline --}}
        <form method="POST" action="{{ route('digest.generate') }}">
          @csrf
          <input type="hidden" name="scope" value="{{ $slug }}">
          <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
            Generate weekly digest (AI)
          </button>
        </form>

        <a href="{{ route('disciplines.show', $slug) }}" class="text-sm text-gray-600 underline">Reset (reload)</a>
      </div>
    </form>
  </div>

  <script>
    const $$ = s => document.querySelectorAll(s);
    const allBtn = document.getElementById('selectAll');
    const noneBtn = document.getElementById('selectNone');

    allBtn?.addEventListener('click', () => $$('input[name="sources[]"]').forEach(cb => cb.checked = true));
    noneBtn?.addEventListener('click', () => $$('input[name="sources[]"]').forEach(cb => cb.checked = false));
  </script>
</body>
</html>
