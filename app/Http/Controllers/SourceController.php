<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Services\SourcePreviewer;

class SourceController extends Controller
{
    public function preview(string $slug, string $key, Request $request, SourcePreviewer $previewer)
    {
        // Validate discipline
        $allDisciplines = array_keys(config('disciplines.all', []));
        abort_unless(in_array($slug, $allDisciplines, true), 404);

        // Find source by key + ensure it belongs to this discipline
        $source = collect(config('sources.list', []))
            ->first(fn($s) => ($s['key'] ?? null) === $key);

        abort_unless($source, 404);
        abort_unless(in_array($slug, $source['disciplines'] ?? [], true), 404);

        $limit = (int) ($request->query('limit', 5));
        $limit = max(1, min($limit, 10)); // cap 10 for demo

        $forceRefresh = (bool) $request->query('fresh', false);

        try {
            $items = $previewer->fetch($source, $limit, $forceRefresh);
        } catch (\Throwable $e) {
            $items = [];
            $error = $e->getMessage();
        }

        return view('sources.preview', [
            'slug'   => $slug,
            'label'  => config("disciplines.all.$slug.label", ucfirst($slug)),
            'source' => $source,
            'items'  => $items,
            'limit'  => $limit,
            'error'  => $error ?? null,
        ]);
    }
}