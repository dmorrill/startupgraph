<?php

namespace Tests\Feature\BulkImport;

use App\Services\BulkImport\BaseBulkImporter;
use Tests\TestCase;

class BaseBulkImporterTest extends TestCase
{
    public function test_status_mapping(): void
    {
        $importer = new class extends BaseBulkImporter {
            public function source(): string { return 'test'; }
            public function import(array $options = []): void {}
            public function publicMapStatus(string $s): string { return $this->mapStatus($s); }
            public function publicExtractDomain(?string $u): ?string { return $this->extractDomain($u); }
        };

        $this->assertEquals('operating', $importer->publicMapStatus('Active'));
        $this->assertEquals('operating', $importer->publicMapStatus('operating'));
        $this->assertEquals('closed', $importer->publicMapStatus('Inactive'));
        $this->assertEquals('closed', $importer->publicMapStatus('closed'));
        $this->assertEquals('closed', $importer->publicMapStatus('dead'));
        $this->assertEquals('acquired', $importer->publicMapStatus('Acquired'));
        $this->assertEquals('ipo', $importer->publicMapStatus('ipo'));
        $this->assertEquals('ipo', $importer->publicMapStatus('public'));
        $this->assertEquals('operating', $importer->publicMapStatus('unknown'));
    }

    public function test_domain_extraction(): void
    {
        $importer = new class extends BaseBulkImporter {
            public function source(): string { return 'test'; }
            public function import(array $options = []): void {}
            public function publicExtractDomain(?string $u): ?string { return $this->extractDomain($u); }
        };

        $this->assertEquals('stripe.com', $importer->publicExtractDomain('https://www.stripe.com'));
        $this->assertEquals('stripe.com', $importer->publicExtractDomain('https://stripe.com/payments'));
        $this->assertNull($importer->publicExtractDomain(null));
        $this->assertNull($importer->publicExtractDomain('not-a-url'));
    }
}
