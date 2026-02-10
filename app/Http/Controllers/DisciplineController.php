<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DisciplineController extends Controller
{
    public function index(Request $request)
    {
        $all = config('disciplines.all');
        $defaults = config('disciplines.enabled_by_default', []);
        $selected = $request->session()->get('enabled_disciplines', $defaults);

        return view('disciplines.index', [
            'all'       => $all,
            'selected'  => $selected,
            'countAll'  => count($all),
            'countSel'  => count($selected),
        ]);
    }

    public function update(Request $request)
    {
        $all = array_keys(config('disciplines.all', []));
        $aliases = config('disciplines.aliases', []);

        $input = (array) $request->input('disciplines', []);
        $normalized = collect($input)
            ->map(fn($k) => strtolower(trim($k)))
            ->map(fn($k) => $aliases[$k] ?? $k)
            ->filter(fn($k) => in_array($k, $all, true))
            ->unique()
            ->values()
            ->all();

        $request->session()->put('enabled_disciplines', $normalized);

        return redirect()->route('disciplines.index')
            ->with('status', 'Selection saved ('.count($normalized).' selected).');
    }

    public function show(string $slug, Request $request)
    {
        $all = config('disciplines.all', []);
        abort_unless(array_key_exists($slug, $all), 404);

        // All sources for this discipline
        $sources = collect(config('sources.list', []))
            ->filter(fn($s) => in_array($slug, $s['disciplines'] ?? [], true))
            ->values();

        // Selected sources (session; default = a small “starter pack” below)
        $sessionKey = "enabled_sources.$slug";
        $selectedSources = $request->session()->get($sessionKey, $this->defaultSourcesFor($slug, $sources));

        return view('disciplines.show', [
            'slug'     => $slug,
            'label'    => $all[$slug]['label'] ?? ucfirst($slug),
            'sources'  => $sources,
            'selected' => $selectedSources,
        ]);
    }

    public function updateSources(string $slug, Request $request)
    {
        $sourceKeysForSlug = collect(config('sources.list', []))
            ->filter(fn($s) => in_array($slug, $s['disciplines'] ?? [], true))
            ->pluck('key')
            ->values()
            ->all();

        $input = (array) $request->input('sources', []);
        $normalized = collect($input)
            ->map(fn($k) => trim($k))
            ->filter(fn($k) => in_array($k, $sourceKeysForSlug, true))
            ->unique()
            ->values()
            ->all();

        $request->session()->put("enabled_sources.$slug", $normalized);

        return back()->with('status', 'Sources updated ('.count($normalized).' selected).');
    }

    private function defaultSourcesFor(string $slug, $sources)
    {
        // Starter pack for math: all + a few subfields + rxiv feeds
        if ($slug === 'math') {
            $preferred = [
                'arxiv_math_all',
                'arxiv_math_PR', // Probability
                'arxiv_math_NT', // Number Theory
                'arxiv_math_AP', // Analysis of PDEs
                'biorxiv_recent',
                'medrxiv_recent',
            ];
            $available = $sources->pluck('key')->all();
            return array_values(array_intersect($preferred, $available));
        }
        // Otherwise: pick the first 3 as sane defaults
        return $sources->pluck('key')->take(3)->all();
    }
}