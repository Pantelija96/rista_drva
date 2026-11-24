<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolGeneration;
use App\Models\Tree;
use App\Models\TreeGroup;
use App\Models\TreeSpecies;
use App\Services\BulkRegisterTrees;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MapTest extends TestCase
{
    use RefreshDatabase;

    private function records(): array
    {
        $school = School::create(['name' => 'Map school', 'city' => 'Beograd', 'municipality' => 'Grocka', 'status' => 'published', 'latitude' => 44.678, 'longitude' => 20.718, 'contact_email' => 'PRIVATE@example.test', 'contact_phone' => 'PRIVATE PHONE']);
        $generation = SchoolGeneration::create(['school_id' => $school->id, 'graduation_year' => 2026, 'planting_model' => 'one_tree_per_graduate', 'status' => 'published', 'notes' => 'PRIVATE GENERATION']);
        $species = TreeSpecies::create(['common_name' => 'Lipa', 'published' => true]);
        $group = TreeGroup::create(['name' => 'Generacija 2026', 'school_id' => $school->id, 'school_generation_id' => $generation->id, 'declared_tree_count' => 30, 'published' => true, 'latitude' => 44.679, 'longitude' => 20.719]);
        $tree = Tree::create(['tree_species_id' => $species->id, 'school_id' => $school->id, 'school_generation_id' => $generation->id, 'tree_group_id' => $group->id, 'latitude' => 44.6791, 'longitude' => 20.7191, 'public_visibility' => true, 'adoption_status' => 'available', 'notes' => 'PRIVATE TREE', 'planted_at' => '2026-10-10']);
        $adopted = Tree::create(['tree_species_id' => $species->id, 'school_id' => $school->id, 'school_generation_id' => $generation->id, 'tree_group_id' => $group->id, 'latitude' => 44.6792, 'longitude' => 20.7192, 'public_visibility' => true, 'adoption_status' => 'adopted']);

        return compact('school', 'generation', 'species', 'group', 'tree', 'adopted');
    }

    public function test_school_tree_group_and_adopted_marker_metadata_are_public_and_distinct(): void
    {
        $records = $this->records();
        $response = $this->getJson('/sr/mapa/podaci')->assertOk()->assertJsonCount(4, 'markers');
        $markers = collect($response->json('markers'))->keyBy('type');
        $this->assertSame($records['school']->id, $markers['school']['school_id']);
        $this->assertSame([2026], $markers['school']['graduation_years']);
        $this->assertSame(30, $markers['group']['declared_tree_count']);
        $this->assertSame(2, $markers['group']['registered_tree_count']);
        $this->assertSame($records['tree']->public_identifier, $markers['tree']['identifier']);
        $this->assertSame('2026-10-10', $markers['tree']['planted_at']);
        $this->assertSame('Lipa', $markers['tree']['species']);
        $this->assertSame('adopted', $markers['adopted']['adoption_status']);
        $response->assertDontSee('PRIVATE')->assertDontSee('contact_email')->assertDontSee('notes');
    }

    public function test_map_filter_parameters_match_published_relationships(): void
    {
        $r = $this->records();
        foreach (['search=Map%20school', 'city=Grocka', 'city=Beograd', 'school_id='.$r['school']->id, 'graduation_year=2026'] as $filter) {
            $this->getJson('/sr/mapa/podaci?'.$filter)->assertOk()->assertJsonCount(4, 'markers');
        }
        foreach (['city=Unknown', 'school_id=99999', 'graduation_year=2025'] as $filter) {
            $this->getJson('/sr/mapa/podaci?'.$filter)->assertOk()->assertJsonCount(0, 'markers');
        }
        $this->getJson('/sr/mapa/podaci?species_id='.$r['species']->id)->assertJsonCount(2, 'markers');
        $this->getJson('/sr/mapa/podaci?category=tree')->assertJsonCount(2, 'markers');
        $this->getJson('/sr/mapa/podaci?category=school')->assertJsonCount(1, 'markers')->assertJsonPath('markers.0.type', 'school');
        $this->getJson('/sr/mapa/podaci?category=group')->assertJsonCount(1, 'markers');
        $this->getJson('/sr/mapa/podaci?category=adopted')->assertJsonCount(1, 'markers')->assertJsonPath('markers.0.type', 'adopted');
        $this->getJson('/sr/mapa/podaci?adoption_status=available')->assertJsonCount(1, 'markers')->assertJsonPath('markers.0.adoption_status', 'available');
        $this->getJson('/sr/mapa/podaci?search='.$r['tree']->public_identifier)->assertJsonCount(1, 'markers');
        $this->getJson('/sr/mapa/podaci?search=Lipa&graduation_year=2026&adoption_status=adopted')->assertJsonCount(1, 'markers');
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->getJson('/sr/mapa/podaci?category=invalid&school_id=-1&graduation_year=abc&species_id[]=1&adoption_status=invalid&city[]=x')->assertUnprocessable()->assertJsonValidationErrors(['category', 'school_id', 'graduation_year', 'species_id', 'adoption_status', 'city']);
    }

    public function test_location_visibility_and_draft_ancestors_are_respected(): void
    {
        $r = $this->records();
        $r['school']->update(['latitude' => null, 'longitude' => null]);
        $this->getJson('/sr/mapa/podaci')->assertJsonCount(3, 'markers');
        $r['tree']->update(['latitude' => null, 'longitude' => null]);
        $this->getJson('/sr/mapa/podaci')->assertJsonCount(2, 'markers');
        $r['generation']->update(['status' => 'draft']);
        $this->getJson('/sr/mapa/podaci')->assertJsonCount(0, 'markers');
        $r['generation']->update(['status' => 'published']);
        $r['school']->update(['status' => 'draft']);
        $this->getJson('/sr/mapa/podaci')->assertJsonCount(0, 'markers');
    }

    public function test_group_total_and_individual_records_are_never_added_together_or_copied_as_locations(): void
    {
        $r = $this->records();
        app(BulkRegisterTrees::class)->handle(['tree_species_id' => $r['species']->id, 'school_id' => $r['school']->id, 'school_generation_id' => $r['generation']->id, 'tree_group_id' => $r['group']->id], 3);
        $response = $this->getJson('/sr/mapa/podaci?category=group')->assertJsonCount(1, 'markers');
        $response->assertJsonPath('markers.0.declared_tree_count', 30)->assertJsonPath('markers.0.registered_tree_count', 5);
        $this->assertSame(3, Tree::whereNull('latitude')->whereNull('longitude')->count());
        $this->getJson('/sr/mapa/podaci')->assertJsonCount(4, 'markers');
    }

    public function test_admin_coordinates_must_be_paired_and_in_wgs84_ranges(): void
    {
        $r = $this->records();
        foreach ([['latitude' => 90.1, 'longitude' => 20], ['latitude' => 44, 'longitude' => 180.1], ['latitude' => 44, 'longitude' => null]] as $coordinates) {
            try {
                $r['tree']->fresh()->update($coordinates);
                $this->fail('Invalid coordinates were accepted.');
            } catch (ValidationException) {
            }
        }
        $r['tree']->fresh()->update(['latitude' => 0, 'longitude' => 0]);
        $this->getJson('/sr/mapa/podaci?search='.$r['tree']->public_identifier)->assertJsonPath('markers.0.lat', 0)->assertJsonPath('markers.0.lng', 0);
    }

    public function test_blade_component_reads_tile_configuration_and_allows_optional_controls(): void
    {
        config(['maps.tile_url' => 'https://tiles.example.test/{z}/{x}/{y}.png', 'maps.attribution' => 'Test provider', 'maps.latitude' => 44.678, 'maps.longitude' => 20.718, 'maps.zoom' => 11, 'maps.max_zoom' => 19]);
        $html = Blade::render('<x-leaflet-map id="test-map" :filters="false" :clustering="false" :fit-to-markers="false" :markers="[]" />');
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $script = (new \DOMXPath($document))->query('//script[@data-map-settings]')->item(0);
        $settings = json_decode($script->textContent, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('https://tiles.example.test/{z}/{x}/{y}.png', $settings['tileUrl']);
        $this->assertSame(['lat' => 44.678, 'lng' => 20.718], $settings['center']);
        $this->assertSame(11, $settings['zoom']);
        $this->assertFalse($settings['clustering']);
        $this->assertFalse($settings['fitToMarkers']);
        $this->assertStringNotContainsString('data-map-filter=', $html);
        $this->assertStringContainsString('id="test-map"', $html);
    }

    public function test_map_queries_do_not_grow_per_marker(): void
    {
        $r = $this->records();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/sr/mapa/podaci')->assertOk();
        $baseline = count(DB::getQueryLog());
        for ($i = 0; $i < 20; $i++) {
            Tree::create(['tree_species_id' => $r['species']->id, 'school_id' => $r['school']->id, 'school_generation_id' => $r['generation']->id, 'latitude' => 44.6 + $i / 1000, 'longitude' => 20.7, 'public_visibility' => true]);
        }
        DB::flushQueryLog();
        $this->getJson('/sr/mapa/podaci')->assertOk();
        $this->assertLessThanOrEqual($baseline + 1, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }
}
