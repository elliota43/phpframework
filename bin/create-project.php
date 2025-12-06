#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Create a new project from the skeleton template
 * 
 * Usage: php bin/create-project.php project-name [destination]
 */

if ($argc < 2) {
    echo "Usage: php bin/create-project.php project-name [destination]\n";
    echo "Example: php bin/create-project.php my-app\n";
    exit(1);
}

$projectName = $argv[1];
$destination = $argv[2] ?? $projectName;

$frameworkRoot = dirname(__DIR__);
$skeletonDir = $frameworkRoot . '/skeleton';
$targetDir = getcwd() . '/' . $destination;

// Check if skeleton exists
if (!is_dir($skeletonDir)) {
    echo "Error: Skeleton directory not found at {$skeletonDir}\n";
    exit(1);
}

// Check if target already exists
if (file_exists($targetDir)) {
    echo "Error: Directory {$targetDir} already exists\n";
    exit(1);
}

echo "Creating new project '{$projectName}' in {$targetDir}...\n";

// Create target directory
mkdir($targetDir, 0755, true);

// Copy skeleton files
copyDirectory($skeletonDir, $targetDir);

// Update composer.json with project name
$composerFile = $targetDir . '/composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    $composer['name'] = strtolower(str_replace(' ', '-', $projectName));
    $composer['description'] = "A new project built with the PHP Framework";
    file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

echo "Project created successfully!\n\n";
echo "Next steps:\n";
echo "1. cd {$destination}\n";
echo "2. composer install\n";
echo "3. cp .env.example .env\n";
echo "4. touch database.sqlite\n";
echo "5. php mini serve\n";

/**
 * Recursively copy directory
 */
function copyDirectory(string $source, string $destination): void
{
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

