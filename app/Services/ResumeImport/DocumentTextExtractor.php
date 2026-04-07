<?php

namespace App\Services\ResumeImport;

use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class DocumentTextExtractor
{
    /**
     * Extract readable text from a document path.
     */
    public function extract(string $path): string
    {
        // PDF (prefer Smalot if installed)
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            // Smalot PDF parser
            if (class_exists('Smalot\\PdfParser\\Parser')) {
                try {
                    $parser = new Parser;
                    $pdf = $parser->parseFile($path);

                    return trim((string) $pdf->getText());
                } catch (\Throwable $_) {
                    // fall through to other strategies
                }
            }

            // pdftotext CLI
            $pdftotext = trim((string) shell_exec('which pdftotext 2>/dev/null'));
            if ($pdftotext !== '') {
                $out = @shell_exec(escapeshellcmd($pdftotext).' -enc UTF-8 '.escapeshellarg($path).' -');
                if (is_string($out) && $out !== '') {
                    return trim($out);
                }
            }

            // strings fallback
            $strings = trim((string) shell_exec('which strings 2>/dev/null'));
            if ($strings !== '') {
                $out = @shell_exec(escapeshellcmd($strings).' '.escapeshellarg($path));
                if (is_string($out) && $out !== '') {
                    return trim($out);
                }
            }

            return '';
        }

        // DOCX: try PhpOffice or simple unzip + document.xml
        if (in_array($ext, ['docx', 'doc'], true)) {
            if ($ext === 'docx') {
                if (class_exists('PhpOffice\\PhpWord\\IOFactory')) {
                    try {
                        $phpWord = IOFactory::load($path);
                        // PhpWord does not provide a simple text dump API reliably,
                        // so fall back to unzip approach if we cannot get text.
                    } catch (\Throwable $_) {
                        // ignore and continue
                    }
                }

                // attempt to read document.xml from the .docx (zip)
                $zip = new \ZipArchive;
                if ($zip->open($path) === true) {
                    $idx = $zip->locateName('word/document.xml', \ZipArchive::FL_NOCASE);
                    if ($idx !== false) {
                        $xml = $zip->getFromIndex($idx);
                        $zip->close();
                        if (is_string($xml)) {
                            // strip tags, convert common breaks to newlines
                            $text = preg_replace('#<(?:/w:t|w:br|br)?>#i', "\n", $xml);
                            $text = strip_tags($text);

                            return trim(preg_replace('/\s+/', ' ', $text));
                        }
                    } else {
                        $zip->close();
                    }
                }
            }

            // For .doc (old binary) we don't have a robust parser here.
            // Return empty and let caller fallback to other strategies.
            return '';
        }

        // Unknown extension: attempt to read as text
        try {
            $contents = @file_get_contents($path);

            return is_string($contents) ? trim($contents) : '';
        } catch (\Throwable $_) {
            return '';
        }
    }
}
