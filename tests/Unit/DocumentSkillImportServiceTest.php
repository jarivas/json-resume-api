<?php

use App\Services\Import\DocumentSkillImportService;
use PHPUnit\Framework\TestCase;

class DocumentSkillImportServiceTest extends TestCase
{
    public function test_parse_certificates_from_text_extracts_name_and_date()
    {
        $svc = new DocumentSkillImportService;

        $text = 'Certificado: Seguridad Informática 2021-09-15. Centro: Instituto Nacional de Ciberseguridad.';

        $method = new ReflectionMethod(DocumentSkillImportService::class, 'parseCertificatesFromText');
        $method->setAccessible(true);

        $result = $method->invoke($svc, $text);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $first = $result[0];
        $this->assertArrayHasKey('name', $first);
        $this->assertStringContainsString('Seguridad Informática', $first['name']);

        // Date should be normalized
        $this->assertArrayHasKey('date', $first);
        $this->assertEquals('2021-09-15', $first['date']);
    }

    public function test_find_date_near_handles_month_names_and_year_only()
    {
        $svc = new DocumentSkillImportService;

        $rm = new ReflectionMethod(DocumentSkillImportService::class, 'findDateNear');
        $rm->setAccessible(true);

        $text = 'Certificado emitido en Septiembre 2020 por la Universidad';
        $date = $rm->invoke($svc, $text, 'Certificado');
        $this->assertEquals('2020-09-01', $date);

        $text2 = 'Some text with year 2018 mentioned nearby';
        $date2 = $rm->invoke($svc, $text2, 'text');
        $this->assertEquals('2018-01-01', $date2);
    }
}
