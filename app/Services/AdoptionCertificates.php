<?php
namespace App\Services;

use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Support\PublicText;
use Dompdf\Dompdf;
use Dompdf\Options;

final class AdoptionCertificates
{
    public function reference(TreeAdoption $adoption): string
    {
        return 'ECO-CERT-'.strtoupper($adoption->public_id);
    }

    public function eligible(TreeAdoption $adoption): bool
    {
        return $adoption->status === 'active' && $adoption->email_verified_at !== null
            && $adoption->approved_at !== null && Tree::published()->whereKey($adoption->tree_id)->exists();
    }

    public function data(TreeAdoption $adoption): array
    {
        abort_unless($this->eligible($adoption), 404, __('certificates.unavailable'));
        $adoption->load('tree.species', 'tree.school', 'tree.generation');
        $tree = $adoption->tree;
        // Explicit safe projection: no model/contact/note/token serialization into the document.
        return $this->treeData($tree) + [
            'reference' => $this->reference($adoption),
            'guardian' => $adoption->displayName(),
            'adopted' => $adoption->approved_at->format('d.m.Y.'),
            'adoptionType' => PublicText::label(TreeAdoption::TYPES[$adoption->adoption_type]),
            'generated' => now()->format('d.m.Y. H:i').' '.config('app.timezone'),
        ];
    }

    public function treeData(Tree $tree): array
    {
        $tree->loadMissing('species', 'school', 'generation');
        $qr = app(TreeQr::class);
        return [
            'identifier' => $tree->public_identifier,
            'species' => $tree->species->localized('common_name'),
            'scientific' => $tree->species->scientific_name,
            'school' => $tree->school?->name,
            'generation' => $tree->generation?->graduation_year,
            'profileUrl' => $qr->publicUrl($tree),
            'qrImage' => 'data:image/png;base64,'.base64_encode($qr->png($tree)),
            'brandImage' => 'data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path('assets/images/favicon.svg'))),
        ];
    }

    public function certificate(TreeAdoption $adoption): string
    {
        return $this->pdf('pdf.certificate', $this->data($adoption));
    }

    public function label(Tree $tree): string
    {
        return $this->pdf('pdf.tree-label', $this->treeData($tree));
    }

    private function pdf(string $view, array $data): string
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('isJavascriptEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', [public_path(), base_path('vendor/dompdf')]);
        $pdf = new Dompdf($options);
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml(view($view, $data)->render(), 'UTF-8');
        $pdf->render();
        return $pdf->output();
    }
}
