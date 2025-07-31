<?php

use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

// Clear any existing test data
DB::table('wishlists')->where('name', 'Test External URL Image')->delete();

// Create a test wishlist item with an external image URL
$wishlist = Wishlist::create([
    'name' => 'Test External URL Image',
    'image' => 'https://via.placeholder.com/640x480.png/0077ff?text=products+test',
    'description' => 'This is a test item with an external image URL',
    'link' => 'https://example.com',
    'cost_per_unit' => 100.00,
    'count' => 1,
    'deadline' => now()->addDays(30),
]);

echo "Created test wishlist item with ID: " . $wishlist->id . "\n";
echo "Image URL: " . $wishlist->image . "\n";
echo "To test, visit:\n";
echo "- Admin index: " . url('/admin/wishlist') . "\n";
echo "- Admin detail: " . url('/admin/wishlist/' . $wishlist->id) . "\n";
echo "- Public index: " . url('/wishlist') . "\n";
echo "- Public detail: " . url('/wishlist/' . $wishlist->id) . "\n";
echo "\nCheck that the image displays correctly in all views without a 403 error.\n";
