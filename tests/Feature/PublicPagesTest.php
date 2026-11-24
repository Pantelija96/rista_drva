<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('pages')]
    public function test_public_page_renders_with_database_content(string $name, string $path, string $view): void
    {
        config(['session.driver' => 'file', 'cache.default' => 'file', 'queue.default' => 'sync']);
        $this->withoutVite();

        $this->assertSame('/sr'.($path === '/' ? '' : $path), route($name, absolute: false));

        $response = $this->get('/sr'.($path === '/' ? '' : $path))
            ->assertOk()
            ->assertViewIs('pages.'.$view)
            ->assertSee('id="header"', false)
            ->assertSee('id="footer"', false)
            ->assertSee('dm drogerie markt')
            ->assertSee('https://s2d2.rs')
            ->assertDontSee('.html"', false)
            ->assertSee("form-action 'self'", false);

        $document = new \DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $navigation = (new \DOMXPath($document))->query('//*[@id="mainNav"]//a[@class]');
        $this->assertCount(6, $navigation);

        foreach ($navigation as $index => $link) {
            $route = self::pages()[$index][0];
            $this->assertSame(route($route), $link->getAttribute('href'));
            $this->assertSame($route === $name ? 'page' : '', $link->getAttribute('aria-current'));
        }
    }

    public static function pages(): array
    {
        return [
            ['home', '/', 'home'],
            ['mission', '/nasa-misija', 'nasa-misija'],
            ['join', '/pridruzi-se', 'pridruzi-se'],
            ['green-trace', '/nas-zeleni-trag', 'nas-zeleni-trag'],
            ['gallery', '/galerija', 'galerija'],
            ['contact', '/kontakt', 'kontakt'],
        ];
    }

    public function test_legacy_links_redirect_to_the_corresponding_pages(): void
    {
        $this->get('/index.html')->assertRedirectToRoute('home')->assertStatus(301);
        $this->get('/mapa.html')->assertRedirect(route('green-trace').'#mapa')->assertStatus(301);
        $this->get('/drvo-za-maturanta.html')->assertRedirectToRoute('join')->assertStatus(301);

        foreach (array_slice(self::pages(), 1) as [$name, $path]) {
            $this->get($path.'.html')->assertRedirectToRoute($name)->assertStatus(301);
        }
    }

    public function test_public_pages_do_not_accept_form_posts(): void
    {
        foreach (self::pages() as [, $path]) {
            if ($path === '/pridruzi-se') {
                continue;
            }
            $this->post($path)->assertMethodNotAllowed();
        }
    }
}
