<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\BulkImport\WikipediaCategoryImporter;

// Create a test importer that only imports a few categories
class TestWikipediaCategoryImporter extends WikipediaCategoryImporter
{
    // Override with just a small subset for testing
    private const TEST_CATEGORIES = [
        'Category:Y_Combinator_companies' => 'operating',
        'Category:Companies_based_in_San_Francisco' => 'operating',
        'Category:Artificial_intelligence_companies' => 'operating',
    ];

    public function source(): string
    {
        return 'wikipedia-categories-test';
    }

    public function import(array $options = []): void
    {
        echo "Testing Wikipedia category import with small subset...\n";

        foreach (self::TEST_CATEGORIES as $category => $status) {
            echo "Testing import from {$category}...\n";
            
            // Get reflection to access private method
            $reflection = new ReflectionClass($this);
            $method = $reflection->getMethod('importCategory');
            $method->setAccessible(true);
            
            // Import only first 5 companies from each category for testing
            $method->invoke($this, $category, $status, 5);
            
            echo "Completed {$category}. Stats: " . json_encode($this->getStats()) . "\n";
            break; // Just test one category for now
        }

        echo "Test import complete. Final stats: " . json_encode($this->getStats()) . "\n";
    }
}

try {
    $importer = new TestWikipediaCategoryImporter();
    $result = $importer->start();
    echo "Import completed successfully!\n";
    echo "Import ID: {$result->id}\n";
    echo "Status: {$result->status}\n";
    echo "Created: {$result->companies_created}\n";
    echo "Updated: {$result->companies_updated}\n";
    echo "Skipped: {$result->companies_skipped}\n";
} catch (Exception $e) {
    echo "Import failed: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}