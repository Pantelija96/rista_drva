<?php
namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Services\AdoptionCertificates;
use App\Services\TreeQr;
use Illuminate\Http\Request;

final class TreeDocumentsController
{
    public function certificate(Request $request, TreeAdoption $adoption, AdoptionCertificates $certificates)
    {
        $this->authorizeCertificate($request, $adoption);
        abort_unless($certificates->eligible($adoption), 404, __('certificates.unavailable'));
        $pdf = $certificates->certificate($adoption);
        // Recheck after rendering in case the adoption or access was revoked meanwhile.
        $adoption->refresh();
        $this->authorizeCertificate($request, $adoption);
        abort_unless($certificates->eligible($adoption), 404, __('certificates.unavailable'));
        return $this->download($pdf, 'application/pdf', $certificates->reference($adoption).'-'.app()->getLocale().'.pdf');
    }

    public function qr(Request $request, string $identifier, TreeQr $qr)
    {
        $tree = Tree::published()->where('public_identifier', $identifier)->firstOrFail();
        return response($qr->svg($tree), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="'.$tree->public_identifier.'-'.app()->getLocale().'.svg"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            // An unpublished tree must not remain accessible through a cached response.
            'Cache-Control' => 'no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    public function label(Request $request, string $identifier, AdoptionCertificates $certificates)
    {
        abort_unless($request->user()?->is_admin, 403);
        $tree = Tree::published()->where('public_identifier', $identifier)->firstOrFail();
        return $this->download($certificates->label($tree), 'application/pdf', $tree->public_identifier.'-label-'.app()->getLocale().'.pdf');
    }

    private function authorizeCertificate(Request $request, TreeAdoption $adoption): void
    {
        if ($request->user()?->is_admin) {
            return;
        }
        $session = $request->session()->get('guardian', []);
        $valid = ($session['id'] ?? null) === $adoption->id
            && ($session['version'] ?? null) === $adoption->access_version
            && ($session['expires_at'] ?? 0) > now()->timestamp
            && $adoption->status === 'active';
        // Do not disclose whether a guessed adoption reference exists or is eligible.
        abort_unless($valid, 404);
    }

    private function download(string $bytes, string $type, string $filename)
    {
        return response($bytes, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
