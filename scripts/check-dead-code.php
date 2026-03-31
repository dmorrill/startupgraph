<?php

/**
 * Simple dead code detection script
 * This script looks for potentially unused methods, classes, and imports
 */

function scanFiles($directory) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

function extractMethods($content) {
    $methods = [];
    if (preg_match_all('/(?:public|private|protected)\s+(?:static\s+)?function\s+(\w+)\s*\(/', $content, $matches)) {
        $methods = array_unique($matches[1]);
        // Remove common Laravel methods that are likely used
        $methods = array_filter($methods, function($method) {
            $commonMethods = ['index', 'show', 'store', 'update', 'destroy', 'create', 'edit', '__construct', '__invoke'];
            return !in_array($method, $commonMethods);
        });
    }
    return $methods;
}

function extractClasses($content) {
    $classes = [];
    if (preg_match_all('/(?:class|interface|trait)\s+(\w+)/', $content, $matches)) {
        $classes = array_unique($matches[1]);
    }
    return $classes;
}

function extractImports($content) {
    $imports = [];
    if (preg_match_all('/^use\s+([^;]+);/m', $content, $matches)) {
        foreach ($matches[1] as $import) {
            // Extract just the class name from the import
            $parts = explode(' as ', $import);
            $import = $parts[0]; // Take the part before 'as' if it exists
            $parts = explode('\\', trim($import));
            $className = end($parts);
            $imports[] = $className;
        }
    }
    return array_unique($imports);
}

function findUsages($searchTerm, $files, $excludeFile = null) {
    $usages = 0;
    foreach ($files as $file) {
        if ($file === $excludeFile) continue;
        
        $content = file_get_contents($file);
        $usages += substr_count($content, $searchTerm);
    }
    return $usages;
}

echo "🔍 StartupGraph Dead Code Detection\n";
echo "===================================\n\n";

$appFiles = scanFiles(__DIR__ . '/../app');
$allContent = '';
foreach ($appFiles as $file) {
    $allContent .= file_get_contents($file);
}

$potentialIssues = [];

// Check each file
foreach ($appFiles as $file) {
    $content = file_get_contents($file);
    $filename = basename($file);
    
    // Skip test files and certain Laravel files
    if (strpos($file, 'Test.php') !== false || 
        strpos($file, '/Middleware/') !== false ||
        strpos($file, '/Providers/') !== false) {
        continue;
    }
    
    // Check methods
    $methods = extractMethods($content);
    foreach ($methods as $method) {
        $usages = findUsages($method, $appFiles, $file);
        if ($usages < 2) { // Less than 2 because the definition counts as 1
            $potentialIssues[] = [
                'type' => 'method',
                'name' => $method,
                'file' => $filename,
                'usages' => $usages
            ];
        }
    }
    
    // Check for unused imports
    $imports = extractImports($content);
    foreach ($imports as $import) {
        // Count usage in this file only (excluding the import line)
        $usageContent = preg_replace('/^use\s+.*' . preg_quote($import) . '.*;$/m', '', $content);
        $usages = substr_count($usageContent, $import);
        
        if ($usages === 0) {
            $potentialIssues[] = [
                'type' => 'import',
                'name' => $import,
                'file' => $filename,
                'usages' => $usages
            ];
        }
    }
}

// Sort and display results
usort($potentialIssues, function($a, $b) {
    return strcmp($a['type'], $b['type']) ?: strcmp($a['file'], $b['file']);
});

$importIssues = array_filter($potentialIssues, fn($issue) => $issue['type'] === 'import');
$methodIssues = array_filter($potentialIssues, fn($issue) => $issue['type'] === 'method');

if (!empty($importIssues)) {
    echo "🚨 Potentially Unused Imports:\n";
    echo "------------------------------\n";
    foreach ($importIssues as $issue) {
        echo sprintf("• %s in %s\n", $issue['name'], $issue['file']);
    }
    echo "\n";
}

if (!empty($methodIssues)) {
    echo "⚠️  Potentially Unused Methods:\n";
    echo "-------------------------------\n";
    foreach ($methodIssues as $issue) {
        echo sprintf("• %s() in %s (found %d usages)\n", 
            $issue['name'], $issue['file'], $issue['usages']);
    }
    echo "\n";
}

if (empty($potentialIssues)) {
    echo "✅ No obvious dead code found!\n\n";
} else {
    echo sprintf("📊 Summary: %d potential issues found\n", count($potentialIssues));
    echo sprintf("   - %d unused imports\n", count($importIssues));
    echo sprintf("   - %d unused methods\n", count($methodIssues));
    echo "\n⚠️  Note: This is a basic scan. Manual review required.\n";
}

echo "\n";