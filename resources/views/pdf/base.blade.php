<!doctype html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'sr-Latn' }}"><head><meta charset="utf-8"><title>@yield('title')</title>
<style>
@page { margin: 17mm; }
body { font-family: "DejaVu Sans", sans-serif; font-size: 9.5pt; color: #173d2c; margin: 0; }
.sheet { background: #f9f7ef; border: 1pt solid #bccbb2; border-top: 6pt solid #173d2c; padding: 9mm; }
.brand { text-align: center; margin-bottom: 5mm; }
.brand img { width: 14mm; height: 14mm; }
.brand-name { font-weight: bold; font-size: 23pt; margin: 2mm 0 1mm; }
.initiative { text-transform: uppercase; letter-spacing: 3px; font-size: 9pt; }
h1 { font-family: "DejaVu Serif", serif; text-align: center; font-size: 22pt; line-height: 1.25; margin: 0 0 5mm; font-weight: normal; }
.statement { line-height: 1.65; text-align: center; margin: 0 0 5mm; }
.tree-id { text-align: center; font-size: 23pt; font-weight: bold; margin: 4mm 0; }
.species { text-align: center; font-size: 13pt; margin: 2mm 0 5mm; }
.details { table-layout:fixed; width: 100%; border-collapse: collapse; margin-bottom: 4mm; }
.details td { vertical-align: top; padding: 2mm 0; border-bottom: .5pt solid #dce2d3; line-height: 1.4; }
.details .caption { width: 40%; font-size: 9pt; color: #48624b; }
.qr-block { table-layout:fixed; width: 100%; border-collapse: collapse; }
.qr-block td { vertical-align: middle; }
.qr { width: 34mm; height: 34mm; }
.url { font-size: 8pt; line-height: 1.7; word-wrap: break-word; }
a { color: #173d2c; }
.small { font-size: 8pt; line-height: 1.6; }
.reference { border-top: 1pt solid #a9b897; margin-top: 5mm; padding-top: 4mm; font-size: 7pt; }
.reference strong { font-family: "DejaVu Sans", sans-serif; font-size: 7pt; }
.label { text-align:center; padding:15mm; }
.label .qr {width:70mm;height:70mm;margin:6mm auto;}
.label h1 {font-size:21pt;margin:5mm 0;}
.sheet {word-wrap:break-word;}
.compact {font-size:8pt;}
.compact .brand img {width:11mm;height:11mm;}
.compact .brand-name {font-size:20pt;margin:1mm 0;}
.compact .reference {margin-top:3mm;padding-top:2mm;}
.compact .species {margin-bottom:3mm;}
.compact h1 {font-size:20pt;}
.compact .brand {margin-bottom:3mm;}
.compact .species {font-size:10pt;}
.compact .details td {padding:1.5mm 0;}
.compact .statement {line-height:1.45;}
.compact .qr {width:32mm;height:32mm;}
</style></head><body>@yield('document')</body></html>
