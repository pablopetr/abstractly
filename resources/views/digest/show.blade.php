<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Weekly Digest</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('disciplines.index') }}" class="text-sm underline text-gray-600">← Back to disciplines</a>
    <h1 class="mt-2 text-2xl font-bold">Weekly Digest</h1>

    @php $hasAny = !empty($digest) && collect($digest)->sum(fn($d)=>count($d['sections'] ?? [])) > 0; @endphp

    @if (!$hasAny)
      <div class="mt-6 p-4 rounded bg-yellow-50 border border-yellow-200 text-yellow-900">
        No content yet. Select a ready discipline and sources, then click
        <span class="font-semibold">Generate weekly digest (AI)</span>.
      </div>
    @else
      @foreach ($digest as $d)
        <h2 class="mt-6 text-xl font-semibold">{{ $d['discipline'] }}</h2>

        @foreach ($d['sections'] as $sec)
          <div class="mt-3 p-4 rounded border bg-white">
            <div class="font-semibold">{{ $sec['source'] }}</div>

            @foreach ($sec['items'] as $it)
              <div class="mt-4 p-3 rounded border bg-gray-50">
                <a href="{{ $it['url'] }}" target="_blank" rel="noopener" class="font-medium underline">
                  {{ $it['title'] }}
                </a>
                @if(!empty($it['summary']))
                  <div class="text-xs text-gray-600 mt-1 line-clamp-3">{{ $it['summary'] }}</div>
                @endif

                <div class="prose prose-sm mt-2 whitespace-pre-wrap">
                  <p><strong>ELI5.</strong> {{ $it['eli5'] ?? '' }}</p>
                  <p><strong>Solo SWE.</strong> {{ $it['swe'] ?? '' }}</p>
                  <p><strong>Investor.</strong> {{ $it['investor'] ?? '' }}</p>
                </div>
              </div>
            @endforeach
          </div>
        @endforeach
      @endforeach
    @endif

    <div class="mt-8 flex gap-3">
      <a href="{{ route('disciplines.index') }}"
         class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-900">
        Edit selection
      </a>
      <form method="POST" action="{{ route('digest.generate') }}">
        @csrf
        <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
          Regenerate digest (AI)
        </button>
      </form>
    </div>
  </div>
</body>
</html>
