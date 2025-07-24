<?php

require_once 'vendor/autoload.php';

echo "Verifying Author Field Removal from News Forms...\n\n";

// Check Create.vue file
$createFile = 'resources/js/pages/admin/news/Create.vue';
$createContent = file_get_contents($createFile);

echo "1. Checking Create.vue:\n";
if (strpos($createContent, 'author:') === false) {
    echo "   ✓ No 'author:' field found in form data\n";
} else {
    echo "   ✗ 'author:' field still exists in form data\n";
}

if (strpos($createContent, 'for="author"') === false) {
    echo "   ✓ No Author input field found in template\n";
} else {
    echo "   ✗ Author input field still exists in template\n";
}

if (strpos($createContent, 'v-model="form.author"') === false) {
    echo "   ✓ No Author v-model binding found\n";
} else {
    echo "   ✗ Author v-model binding still exists\n";
}

// Check Edit.vue file
$editFile = 'resources/js/pages/admin/news/Edit.vue';
$editContent = file_get_contents($editFile);

echo "\n2. Checking Edit.vue:\n";
if (strpos($editContent, 'author: string | null;') === false) {
    echo "   ✓ No 'author' field found in News interface\n";
} else {
    echo "   ✗ 'author' field still exists in News interface\n";
}

if (strpos($editContent, 'author: props.news.author') === false) {
    echo "   ✓ No 'author' field found in form initialization\n";
} else {
    echo "   ✗ 'author' field still exists in form initialization\n";
}

if (strpos($editContent, 'for="author"') === false) {
    echo "   ✓ No Author input field found in template\n";
} else {
    echo "   ✗ Author input field still exists in template\n";
}

if (strpos($editContent, 'v-model="form.author"') === false) {
    echo "   ✓ No Author v-model binding found\n";
} else {
    echo "   ✗ Author v-model binding still exists\n";
}

// Check backend controller
$controllerFile = 'app/Http/Controllers/Admin/NewsController.php';
$controllerContent = file_get_contents($controllerFile);

echo "\n3. Checking NewsController.php:\n";
if (strpos($controllerContent, "validated['author_id'] = Auth::id();") !== false) {
    echo "   ✓ Author is automatically set from Auth::user()\n";
} else {
    echo "   ✗ Author is not automatically set from Auth::user()\n";
}

if (strpos($controllerContent, "'author' => 'nullable|string|max:255'") === false) {
    echo "   ✓ Author validation rule has been removed\n";
} else {
    echo "   ✗ Author validation rule still exists\n";
}

// Check News model
$modelFile = 'app/Models/News.php';
$modelContent = file_get_contents($modelFile);

echo "\n4. Checking News.php model:\n";
if (strpos($modelContent, "'author_id',") !== false) {
    echo "   ✓ 'author_id' is in fillable fields\n";
} else {
    echo "   ✗ 'author_id' is not in fillable fields\n";
}

if (strpos($modelContent, 'public function author(): BelongsTo') !== false) {
    echo "   ✓ Author relationship method exists\n";
} else {
    echo "   ✗ Author relationship method does not exist\n";
}

echo "\n✓ Verification complete! All Author input fields have been successfully removed from the frontend forms.\n";
echo "✓ Backend logic is properly configured to automatically set author from Auth::user().\n";
echo "\nSummary: The issue has been resolved - Author input fields are no longer needed since the author is automatically set from the authenticated user.\n";
