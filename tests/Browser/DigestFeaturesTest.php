<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DigestFeaturesTest extends DuskTestCase
{
    /**
     * Helper: select math discipline with biorxiv source and generate a digest.
     */
    private function generateDigest(Browser $browser): void
    {
        $browser->visit('/disciplines')
            ->click('[wire\:click="selectAll"]')
            ->pause(300)
            ->click('[wire\:click="save"]')
            ->waitForText('Selection saved');

        $browser->visit('/disciplines/math')
            ->click('[wire\:click="selectNone"]')
            ->pause(300)
            ->click('[wire\:click="toggleSource(\'biorxiv_recent\')"]')
            ->pause(300)
            ->click('[wire\:click="save"]')
            ->waitForText('Sources updated');

        $browser->visit('/digest')
            ->click('[wire\:click="generate"]')
            ->waitForText('ELI5', 60);
    }

    // ------------------------------------------------------------------
    // Export JSON button
    // ------------------------------------------------------------------

    public function test_export_button_appears_after_generation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->generateDigest($browser);

            $browser->assertSee('Export JSON')
                ->assertPresent('[wire\:click="export"]');
        });
    }

    public function test_export_button_hidden_when_no_digest(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->assertDontSee('Export JSON');
        });
    }

    // ------------------------------------------------------------------
    // Skip cache checkbox
    // ------------------------------------------------------------------

    public function test_skip_cache_checkbox_is_present(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->assertSee('Skip cache')
                ->assertPresent('input[wire\:model="forceRefresh"]');
        });
    }

    public function test_skip_cache_checkbox_is_toggleable(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->assertNotChecked('input[wire\:model="forceRefresh"]')
                ->check('input[wire\:model="forceRefresh"]')
                ->assertChecked('input[wire\:model="forceRefresh"]');
        });
    }

    // ------------------------------------------------------------------
    // Streaming / progressive rendering
    // ------------------------------------------------------------------

    public function test_progress_status_element_exists_during_generation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Selection saved');

            $browser->visit('/disciplines/math')
                ->click('[wire\:click="selectNone"]')
                ->pause(300)
                ->click('[wire\:click="toggleSource(\'biorxiv_recent\')"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Sources updated');

            // Verify the progress status container is in the DOM
            $browser->visit('/digest')
                ->assertPresent('[wire\:stream="progress-status"]')
                ->assertPresent('[wire\:stream="digest-stream"]');
        });
    }

    // ------------------------------------------------------------------
    // Dedup also_in badges
    // ------------------------------------------------------------------

    public function test_also_in_badges_appear_for_cross_listed_papers(): void
    {
        $this->browse(function (Browser $browser) {
            // Select math with two arXiv sources that return the same canned papers
            $browser->visit('/disciplines')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Selection saved');

            $browser->visit('/disciplines/math')
                ->click('[wire\:click="selectNone"]')
                ->pause(300)
                ->click('[wire\:click="toggleSource(\'arxiv_math_all\')"]')
                ->pause(300)
                ->click('[wire\:click="toggleSource(\'arxiv_math_AG\')"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Sources updated');

            // Generate — dedup should collapse duplicate arXiv papers and show also_in
            $browser->visit('/digest')
                ->click('[wire\:click="generate"]')
                ->waitForText('ELI5', 60)
                ->assertSee('Also in:');
        });
    }

    // ------------------------------------------------------------------
    // Session lifetime warning
    // ------------------------------------------------------------------

    public function test_session_lifetime_warning_shown_on_discipline_picker(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->assertSee('Selections are stored in your browser session');
        });
    }

    public function test_session_lifetime_warning_shown_on_source_picker(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->assertSee('Selections are stored in your browser session');
        });
    }

    // ------------------------------------------------------------------
    // Failure warning banner
    // ------------------------------------------------------------------

    public function test_failure_banner_not_shown_on_successful_generation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->generateDigest($browser);

            $browser->assertDontSee('failed during generation');
        });
    }

    // ------------------------------------------------------------------
    // Saved papers — bookmark button
    // ------------------------------------------------------------------

    public function test_bookmark_button_present_on_generated_digest(): void
    {
        $this->browse(function (Browser $browser) {
            $this->generateDigest($browser);

            $browser->assertPresent('[wire\:click^="toggleSave"]');
        });
    }

    public function test_bookmark_toggle_saves_paper_to_saved_page(): void
    {
        $this->browse(function (Browser $browser) {
            $this->generateDigest($browser);

            // Click the first bookmark button to save a paper
            $browser->click('[wire\:click^="toggleSave"]')
                ->pause(500);

            // Navigate to saved page and verify the paper appears
            $browser->visit('/saved')
                ->waitForText('Saved Papers')
                ->assertSee('1 paper saved')
                ->assertPresent('[wire\:click^="removePaper"]');
        });
    }

    public function test_saved_page_shows_empty_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/saved')
                ->assertSee('No saved papers')
                ->assertSee('Bookmark papers from your');
        });
    }

    // ------------------------------------------------------------------
    // Teardown — clean saved papers file
    // ------------------------------------------------------------------

    protected function tearDown(): void
    {
        @unlink(storage_path('app/saved-papers.json'));
        parent::tearDown();
    }
}
