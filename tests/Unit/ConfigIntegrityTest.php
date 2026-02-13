<?php

namespace Tests\Unit;

use Tests\TestCase;

class ConfigIntegrityTest extends TestCase
{
    // ------------------------------------------------------------------
    // Source keys
    // ------------------------------------------------------------------

    public function test_all_source_keys_are_unique(): void
    {
        $sources = config('sources.list', []);
        $keys = array_column($sources, 'key');

        $this->assertSame(
            count($keys),
            count(array_unique($keys)),
            'Duplicate source keys found: ' . implode(', ', array_diff_assoc($keys, array_unique($keys)))
        );
    }

    // ------------------------------------------------------------------
    // Required fields
    // ------------------------------------------------------------------

    public function test_all_sources_have_required_fields(): void
    {
        $required = ['key', 'label', 'url', 'disciplines'];
        $sources = config('sources.list', []);

        foreach ($sources as $i => $source) {
            foreach ($required as $field) {
                $this->assertArrayHasKey(
                    $field,
                    $source,
                    "Source at index {$i} (key: " . ($source['key'] ?? '???') . ") is missing required field '{$field}'"
                );
            }
        }
    }

    public function test_all_sources_have_non_empty_url(): void
    {
        $sources = config('sources.list', []);

        foreach ($sources as $source) {
            $this->assertNotEmpty(
                trim($source['url'] ?? ''),
                "Source '{$source['key']}' has an empty URL"
            );
        }
    }

    public function test_all_sources_have_at_least_one_discipline(): void
    {
        $sources = config('sources.list', []);

        foreach ($sources as $source) {
            $this->assertNotEmpty(
                $source['disciplines'],
                "Source '{$source['key']}' has no disciplines"
            );
        }
    }

    // ------------------------------------------------------------------
    // Discipline references
    // ------------------------------------------------------------------

    public function test_all_source_disciplines_exist_in_disciplines_config(): void
    {
        $validSlugs = array_keys(config('disciplines.all', []));
        $sources = config('sources.list', []);

        foreach ($sources as $source) {
            foreach ($source['disciplines'] as $slug) {
                $this->assertContains(
                    $slug,
                    $validSlugs,
                    "Source '{$source['key']}' references nonexistent discipline '{$slug}'"
                );
            }
        }
    }

    // ------------------------------------------------------------------
    // Disciplines config
    // ------------------------------------------------------------------

    public function test_all_disciplines_have_label(): void
    {
        $disciplines = config('disciplines.all', []);

        foreach ($disciplines as $slug => $meta) {
            $this->assertArrayHasKey(
                'label',
                $meta,
                "Discipline '{$slug}' is missing 'label'"
            );
            $this->assertNotEmpty(
                trim($meta['label']),
                "Discipline '{$slug}' has an empty label"
            );
        }
    }

    public function test_all_disciplines_have_ready_flag(): void
    {
        $disciplines = config('disciplines.all', []);

        foreach ($disciplines as $slug => $meta) {
            $this->assertArrayHasKey(
                'ready',
                $meta,
                "Discipline '{$slug}' is missing 'ready' flag"
            );
            $this->assertIsBool(
                $meta['ready'],
                "Discipline '{$slug}' 'ready' flag should be boolean"
            );
        }
    }
}
