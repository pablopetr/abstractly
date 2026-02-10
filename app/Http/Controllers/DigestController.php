<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SourcePreviewer;
use App\Services\AiSummarizer;

class DigestController extends Controller
{
    public function generate(Request $request, SourcePreviewer $previewer, AiSummarizer $ai)
    {
        $discAll   = config('disciplines.all', []);
        $readyDisc = collect($discAll)->filter(fn ($m) => $m['ready'] ?? false)->keys()->all();

        $scope = trim((string) $request->input('scope', ''));
        if ($scope !== '' && in_array($scope, $readyDisc, true)) {
            $disciplines = [$scope];
        } else {
            $enabled     = (array) $request->session()->get('enabled_disciplines', []);
            $disciplines = array_values(array_intersect($enabled, $readyDisc));
        }

        $limitPerSource = (int) $request->input('limit', 5);
        if ($limitPerSource < 1 || $limitPerSource > 10) $limitPerSource = 5;

        $allSources = collect(config('sources.list', []));
        $digest = [];

        foreach ($disciplines as $slug) {
            $selectedKeys = (array) $request->session()->get("enabled_sources.$slug", []);
            if (empty($selectedKeys)) continue;

            $sourcesForSlug = $allSources
                ->filter(fn ($s) => in_array($slug, $s['disciplines'] ?? [], true))
                ->whereIn('key', $selectedKeys)
                ->values();

            if ($sourcesForSlug->isEmpty()) continue;

            $sections = [];
            foreach ($sourcesForSlug as $src) {
                try {
                    $items = $previewer->fetch($src, $limitPerSource);
                } catch (\Throwable $e) {
                    $items = [];
                }
                if (empty($items)) continue;

                try {
                    $enriched = $ai->summarizeItems($src['label'], $items);
                } catch (\Throwable $e) {
                    $enriched = $items;
                }

                $sections[] = [
                    'source' => $src['label'],
                    'items'  => $enriched,
                ];
            }

            if (!empty($sections)) {
                $digest[] = [
                    'discipline' => $discAll[$slug]['label'] ?? ucfirst($slug),
                    'slug'       => $slug,
                    'sections'   => $sections,
                ];
            }
        }

        $request->session()->put('digest.latest', $digest);
        return redirect()->route('digest.show');
    }

    public function show(Request $request)
    {
        $digest = $request->session()->get('digest.latest', []);
        return view('digest.show', ['digest' => $digest]);
    }
}
