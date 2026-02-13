<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Services\SourcePreviewer;
use App\Services\AiSummarizer;
use App\Services\PaperDeduplicator;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
#[Title('Weekly Digest')]
class DigestViewer extends Component
{
    public array $digest = [];
    public array $failures = [];
    public bool $generating = false;
    public bool $forceRefresh = false;
    public int $limitPerSource = 5;

    protected int $requestTimeout = 120;

    public function mount(): void
    {
        $this->digest = session('digest.latest', []);
    }

    public function generate(SourcePreviewer $previewer, AiSummarizer $ai, PaperDeduplicator $deduplicator): void
    {
        $this->generating = true;
        $this->failures = [];

        $discAll = config('disciplines.all', []);
        $readyDisc = collect($discAll)
            ->filter(fn ($m) => $m['ready'] ?? false)
            ->keys()
            ->all();

        $enabled = (array) session('enabled_disciplines', []);
        $disciplines = array_values(array_intersect($enabled, $readyDisc));

        $allSources = collect(config('sources.list', []));
        $digest = [];

        foreach ($disciplines as $slug) {
            $discLabel = $discAll[$slug]['label'] ?? ucfirst($slug);

            $sourcesForSlug = $allSources
                ->filter(fn ($s) => in_array($slug, $s['disciplines'] ?? [], true))
                ->values();

            $selectedKeys = (array) session("enabled_sources.$slug", []);
            if (! empty($selectedKeys)) {
                $sourcesForSlug = $sourcesForSlug->whereIn('key', $selectedKeys)->values();
            } else {
                $sourcesForSlug = $sourcesForSlug->take(3);
            }

            if ($sourcesForSlug->isEmpty()) {
                continue;
            }

            // Pass 1: Fetch all sources for this discipline.
            $fetchedBySource = [];
            foreach ($sourcesForSlug as $src) {
                $this->stream('progress-status', "Fetching {$src['label']}…", true);

                try {
                    $items = $previewer->fetch($src, $this->limitPerSource, $this->forceRefresh);
                } catch (\Throwable $e) {
                    $this->failures[] = ['source' => $src['label'], 'type' => 'fetch'];
                    $items = [];
                }

                if (! empty($items)) {
                    $fetchedBySource[$src['label']] = $items;
                }
            }

            // Pass 2: Deduplicate across sources within this discipline.
            $this->stream('progress-status', "Deduplicating {$discLabel}…", true);
            $dedupedBySource = $deduplicator->dedup($fetchedBySource);

            // Pass 3: Summarize unique items per source (original order).
            $sections = [];
            foreach ($sourcesForSlug as $src) {
                $items = $dedupedBySource[$src['label']] ?? [];
                if (empty($items)) {
                    continue;
                }

                $this->stream('progress-status', "Summarizing {$src['label']}…", true);

                try {
                    $enriched = $ai->summarizeItems($src['label'], $items, $this->forceRefresh);
                } catch (\Throwable $e) {
                    $this->failures[] = ['source' => $src['label'], 'type' => 'summarize'];
                    $enriched = $items;
                }

                $sections[] = [
                    'source' => $src['label'],
                    'items'  => $enriched,
                ];
            }

            if (! empty($sections)) {
                $entry = [
                    'discipline' => $discLabel,
                    'slug'       => $slug,
                    'sections'   => $sections,
                ];
                $digest[] = $entry;

                $html = view('livewire.partials.digest-section', ['d' => $entry])->render();
                $this->stream('digest-stream', $html, false);
            }
        }

        session(['digest.latest' => $digest]);
        $this->digest = $digest;
        $this->generating = false;
    }

    public function export()
    {
        $enabled = (array) session('enabled_disciplines', []);
        $sourcesPerDiscipline = [];
        foreach ($enabled as $slug) {
            $sourcesPerDiscipline[$slug] = (array) session("enabled_sources.$slug", []);
        }

        $envelope = [
            'meta' => [
                'generated_at'  => now()->toIso8601String(),
                'format_version' => 1,
                'disciplines'   => $enabled,
                'sources'       => $sourcesPerDiscipline,
            ],
            'digest' => $this->digest,
        ];

        $json = json_encode($envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'digest-' . now()->format('Y-m-d_His') . '.json';

        Storage::disk('local')->put("digests/{$filename}", $json);

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function render()
    {
        return view('livewire.digest-viewer');
    }
}
