<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview — {{ $source['label'] }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('disciplines.show', $slug) }}" class="text-sm underline text-gray-600">← Back to {{ $label }}</a>

    <h1 class="mt-2 text-2xl font-bold">Preview: {{ $source['label'] }}</h1>
    <p class="text-sm text-gray-600">
      Showing up to {{ $limit }} items from <code>{{ $source['url'] }}</code>.
    </p>

    @if ($error ?? false)
      <div class="mt-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
        Error: {{ $error }}
      </div>
    @endif
    @if (empty($items))
      <div class="mt-6 rounded bg-yellow-50 border border-yellow-200 p-3 text-yellow-800">
        No items returned.
      </div>
    @else
      <ol class="mt-6 space-y-3">
        @foreach ($items as $i => $it)
          <li class="p-4 rounded border bg-white">
            <div class="text-sm text-gray-500">#{{ $i + 1 }}</div>
            <div class="font-semibold">{{ $it['title'] ?? '(untitled)' }}</div>
            @if (!empty($it['url']))
              <a href="{{ $it['url'] }}" target="_blank" class="text-blue-600 underline text-sm">Open</a>
            @endif
            @if (!empty($it['summary']))
              <p class="mt-2 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($it['summary'], 400) }}</p>
            @endif
          </li>
        @endforeach
      </ol>
    @endif
  </div>
</body>
</html>