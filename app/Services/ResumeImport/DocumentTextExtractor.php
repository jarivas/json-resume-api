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
                    $text = trim((string) $pdf->getText());

                    if ($this->isReadableText($text)) {
                        return $text;
                    }
                } catch (\Throwable $_) {
                    // fall through to other strategies
                }
            }

            // pdftotext CLI
            $pdftotext = trim((string) shell_exec('which pdftotext 2>/dev/null'));
            if ($pdftotext !== '') {
                $out = @shell_exec(escapeshellcmd($pdftotext).' -enc UTF-8 '.escapeshellarg($path).' -');
                if (is_string($out) && $this->isReadableText($out)) {
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

    /**
     * Determine whether extracted text is human-readable and not binary/garbage.
     * Rejects raw PDF binary (starts with %PDF), null-byte content, and text
     * where fewer than 85% of sampled bytes are printable characters.
     */
    private function isReadableText(string $text): bool
    {
        if (strlen($text) < 20) {
            return false;
        }

        if (str_starts_with($text, '%PDF') || str_starts_with($text, "\x00")) {
            return false;
        }

        $sample = substr($text, 0, 500);
        $len = strlen($sample);
        $printable = 0;

        for ($i = 0; $i < $len; $i++) {
            $byte = ord($sample[$i]);
            if ($byte >= 0x20 || $byte === 0x09 || $byte === 0x0A || $byte === 0x0D) {
                $printable++;
            }
        }

        return ($printable / $len) >= 0.85;
    }
}
