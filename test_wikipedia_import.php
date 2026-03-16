<?php

require_once 'vendor/autoload.php';

use App\Services\BulkImport\WikipediaCategoryImporter;

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the importer with a small subset
$importer = new WikipediaCategoryImporter();

// Test extractCategoryMetadata method
echo "Testing category metadata extraction:\n";

$testCategories = [
    'Category:Technology_companies_established_in_2020',
    'Category:American_companies_established_in_2021',
    'Category:Companies_based_in_San_Francisco',
    'Category:Software_companies_of_the_United_States',
    'Category:Artificial_intelligence_companies',
    'Category:Y_Combinator_companies',
];

$reflection = new ReflectionClass($importer);
$method = $reflection->getMethod('extractCategoryMetadata');
$method->setAccessible(true);

foreach ($testCategories as $category) {
    $metadata = $method->invoke($importer, $category);
    echo "Category: {$category}\n";
    echo "  Year: " . ($metadata['year'] ?: 'null') . "\n";
    echo "  Country: " . ($metadata['country'] ?: 'null') . "\n";
    echo "  City: " . ($metadata['city'] ?: 'null') . "\n";
    echo "  Category: " . ($metadata['category'] ?: 'null') . "\n";
    echo "\n";
}

// Test validation method
echo "Testing company validation:\n";

$testCompanies = [
    ['title' => 'Google', 'extract' => 'Google is a technology company founded in 1998...'],
    ['title' => 'List of companies', 'extract' => 'This is a list of various companies...'],
    ['title' => 'Technology', 'extract' => 'Technology may refer to various applications...'],
    ['title' => 'Stripe', 'extract' => 'Stripe is a payment processing company...'],
    ['title' => '123', 'extract' => 'Some numeric entry'],
    ['title' => 'Airbnb', 'extract' => 'Airbnb is a platform for short-term rentals founded in 2008...'],
];

$validationMethod = $reflection->getMethod('isValidCompany');
$validationMethod->setAccessible(true);

foreach ($testCompanies as $test) {
    $isValid = $validationMethod->invoke($importer, $test['title'], $test['extract']);
    echo "Title: {$test['title']} - " . ($isValid ? 'VALID' : 'INVALID') . "\n";
}

echo "\nTest completed successfully!\n";