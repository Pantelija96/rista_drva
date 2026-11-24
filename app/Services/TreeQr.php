<?php
namespace App\Services;

use App\Models\Tree;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

final class TreeQr
{
    public function publicUrl(Tree $tree, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        abort_unless(in_array($locale, ['sr', 'en'], true), 404);
        abort_unless(Tree::published()->whereKey($tree->id)->exists(), 404);
        $base = rtrim(config('app.url'), '/');
        if (! filter_var($base, FILTER_VALIDATE_URL) || ! in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new \RuntimeException('A valid public APP_URL is required for QR generation.');
        }
        // Use the configured canonical origin, never the incoming Host header or query string.
        $path = route('trees.show', ['locale' => $locale, 'identifier' => $tree->public_identifier], absolute: false);
        $basePath = rtrim(parse_url($base, PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '' && str_starts_with($path, $basePath.'/')) {
            $path = substr($path, strlen($basePath));
        }
        return $base.'/'.ltrim($path, '/');
    }

    public function svg(Tree $tree, ?string $locale = null): string
    {
        return (new Writer(new ImageRenderer(new RendererStyle(600, 4), new SvgImageBackEnd())))
            ->writeString($this->publicUrl($tree, $locale), 'UTF-8', ErrorCorrectionLevel::M());
    }

    public function png(Tree $tree, ?string $locale = null): string
    {
        return (new Writer(new GDLibRenderer(900, 4)))
            ->writeString($this->publicUrl($tree, $locale), 'UTF-8', ErrorCorrectionLevel::M());
    }
}
