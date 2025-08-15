<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Enum\AnnouncementType;
use App\Enum\Role;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RecurringBannerSeeder extends Seeder
{
    /**
     * Run the database seeds for recurring banners.
     */
    public function run(): void
    {
        // Common holidays and events
        $this->seedHolidays();

        // Gaming-related recurring events
        $this->seedGamingEvents();

        // Community-specific recurring events
        $this->seedCommunityEvents();
    }

    /**
     * Seed recurring banners for common holidays
     */
    private function seedHolidays(): void
    {
        // New Year's Day
        $this->createRecurringBanner(
            'New Year\'s Day',
            'Happy New Year! The gaming hub wishes you a fantastic year ahead.',
            1, 1, // January 1
            1, 3, // January 3
            AnnouncementType::PRIMARY
        );

        // Valentine's Day
        $this->createRecurringBanner(
            'Valentine\'s Day',
            'Share the love of gaming! Special couples event today.',
            2, 14, // February 14
            2, 14, // February 14
            AnnouncementType::INFO
        );

        // Halloween
        $this->createRecurringBanner(
            'Halloween Gaming Night',
            'Join us for a spooky gaming session tonight!',
            10, 31, // October 31
            10, 31, // October 31
            AnnouncementType::WARNING,
            'https://example.com/halloween-no',
            'https://example.com/halloween-en'
        );

        // Christmas
        $this->createRecurringBanner(
            'Christmas Holiday',
            'Merry Christmas! The hub will be closed on December 25th.',
            12, 24, // December 24
            12, 26, // December 26
            AnnouncementType::INFO
        );
    }

    /**
     * Seed recurring banners for gaming-related events
     */
    private function seedGamingEvents(): void
    {
        // E3 (typically in June)
        $this->createRecurringBanner(
            'E3 Gaming Conference',
            'Stay tuned for the latest announcements from E3!',
            6, 10, // June 10
            6, 15, // June 15
            AnnouncementType::PRIMARY
        );

        // Gamescom (typically in August)
        $this->createRecurringBanner(
            'Gamescom',
            'Gamescom is happening now! Check out the latest gaming news.',
            8, 23, // August 23
            8, 27, // August 27
            AnnouncementType::INFO
        );

        // Tokyo Game Show (typically in September)
        $this->createRecurringBanner(
            'Tokyo Game Show',
            'Tokyo Game Show is live! Follow our updates for the latest announcements.',
            9, 15, // September 15
            9, 18, // September 18
            AnnouncementType::PRIMARY
        );
    }

    /**
     * Seed recurring banners for community-specific events
     */
    private function seedCommunityEvents(): void
    {
        // Monthly game tournament
        $this->createRecurringBanner(
            'Monthly Tournament',
            'Our monthly gaming tournament is this weekend! Register now.',
            null, null, // Use current month and day
            null, null, // Use current month and day
            AnnouncementType::WARNING,
            null,
            null,
            true, // First Saturday of every month
            [Role::MEMBER->value, Role::ADMIN->value] // Only visible to members and admins
        );

        // Quarterly membership renewal reminder
        $this->createRecurringBanner(
            'Membership Renewal',
            'Don\'t forget to renew your membership for the next quarter!',
            3, 15, // March 15
            3, 31, // March 31
            AnnouncementType::WARNING,
            null,
            null,
            false,
            [Role::MEMBER->value]
        );

        $this->createRecurringBanner(
            'Membership Renewal',
            'Don\'t forget to renew your membership for the next quarter!',
            6, 15, // June 15
            6, 30, // June 30
            AnnouncementType::WARNING,
            null,
            null,
            false,
            [Role::MEMBER->value]
        );

        $this->createRecurringBanner(
            'Membership Renewal',
            'Don\'t forget to renew your membership for the next quarter!',
            9, 15, // September 15
            9, 30, // September 30
            AnnouncementType::WARNING,
            null,
            null,
            false,
            [Role::MEMBER->value]
        );

        $this->createRecurringBanner(
            'Membership Renewal',
            'Don\'t forget to renew your membership for the next quarter!',
            12, 15, // December 15
            12, 31, // December 31
            AnnouncementType::WARNING,
            null,
            null,
            false,
            [Role::MEMBER->value]
        );
    }

    /**
     * Helper method to create a recurring banner
     */
    private function createRecurringBanner(
        string $title,
        string $description,
        ?int $fromMonth,
        ?int $fromDay,
        ?int $toMonth,
        ?int $toDay,
        AnnouncementType $type,
        ?string $linkNorwegian = null,
        ?string $linkEnglish = null,
        bool $isSpecialDate = false,
        ?array $visibleToRole = null
    ): void {
        // If special date logic is needed (like first Saturday of month)
        if ($isSpecialDate) {
            // Example: First Saturday of current month
            $date = Carbon::now()->firstOfMonth()->next(Carbon::SATURDAY);
            $fromMonth = $date->month;
            $fromDay = $date->day;
            $toMonth = $date->month;
            $toDay = $date->day;
        }

        // Create from and to dates using the current year
        $fromDate = Carbon::createFromDate(now()->year, $fromMonth, $fromDay)->startOfDay();
        $toDate = Carbon::createFromDate(now()->year, $toMonth, $toDay)->endOfDay();

        Banner::factory()->create([
            'is_published' => true,
            'is_recurring' => true,
            'from_datetime' => $fromDate,
            'to_datetime' => $toDate,
            'title' => $title,
            'description' => $description,
            'type' => $type->value,
            'visible_to_role' => $visibleToRole,
            'link_norwegian' => $linkNorwegian,
            'link_english' => $linkEnglish,
        ]);
    }
}
