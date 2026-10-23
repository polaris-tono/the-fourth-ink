<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\ArticleReview;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::exists()) {
            $this->command->info('Articles already exist; skipping sample content.');
            return;
        }

        $admin = User::where('email', 'admin@fourthink.test')->first();
        $writer = User::where('email', 'contributor@fourthink.test')->first();

        if (! $admin || ! $writer) {
            $this->command->error('Run UserSeeder first (php artisan db:seed).');
            return;
        }

        $cats = Category::pluck('id', 'slug');

        // [category, type, author, status, title, excerpt, body, featured, views, hoursAgo]
        $rows = [
            // ---- Published campus news (admin) ----
            ['campus', 'news', $admin, 'published', 'Library Extends Opening Hours for Exam Season',
                'Students can now study until late evening throughout the exam period.',
                "The central library will stay open later during the exam period, giving students more time for quiet study.\n\nStaff say reading rooms and the computer lab will both remain available, and extra seating has been added.",
                true, 240, 6],
            ['campus', 'news', $admin, 'published', 'Robotics Club Opens Applications for New Members',
                'First-year and second-year students are encouraged to apply.',
                "The robotics club is recruiting new members for the coming semester. No previous experience is required.\n\nApplicants will take part in a short introductory workshop before joining a project team.",
                false, 95, 30],
            ['campus', 'news', $admin, 'published', 'Call for Project Proposals Ahead of the Annual Tech Fest',
                'Teams have until the end of the month to submit their ideas.',
                "Organisers have opened submissions for the annual tech fest project showcase.\n\nProposals should include a short description, the team members and the equipment required.",
                false, 130, 52],

            // ---- Published Writers' Block pieces (contributor) ----
            ['technology', 'opinion', $writer, 'published', 'Why Every Student Should Learn Basic Version Control',
                'Git is not just for professional developers; it is a safety net for every project.',
                "Most of us have lost work to a crash, an overwritten file or a file named `final_final_v3`. Version control solves this problem quietly and reliably.\n\nLearning the basics takes an afternoon, and the habit pays off in every group project you will ever do.",
                true, 310, 10],
            ['entertainment', 'review', $writer, 'published', 'A Slow-Burn Thriller That Rewards Patience',
                'This quiet film trusts its audience, and is better for it.',
                "The opening half hour asks for patience, but the payoff is worth the wait. The direction is restrained, the sound design is superb, and the final scene lingers.\n\n**Verdict:** a thoughtful thriller best watched without distractions.",
                false, 180, 20],
            ['technology', 'analysis', $writer, 'published', 'What On-Device AI Means for Everyday Apps',
                'Running models locally changes privacy, speed and cost.',
                "When more processing happens on the phone itself, apps can respond faster and keep more data private.\n\nThe trade-offs are battery life, storage and the gap between small local models and large cloud ones.",
                false, 205, 36],
            ['sports', 'feature', $writer, 'published', 'Inside a Village Football Tournament',
                'Muddy pitches, loud crowds and a trophy that means everything.',
                "Long before kick-off, the field is already surrounded by families. Teams arrive on rickshaws and in borrowed vans.\n\nBy the final whistle, the winners are carried off the pitch, and the losers are already planning next year.",
                true, 160, 14],
            ['business', 'analysis', $writer, 'published', 'Small Businesses and the Shift to Digital Payments',
                'Mobile payments are lowering barriers, but not for everyone.',
                "Shopkeepers who once relied on cash now accept mobile payments, which simplifies record keeping.\n\nHowever, transaction fees and patchy connectivity remain real obstacles for the smallest sellers.",
                false, 120, 60],
            ['national', 'opinion', $writer, 'published', 'Public Libraries Deserve More Attention',
                'Quiet, free and open to all: why libraries still matter.',
                "A good library is more than a room full of books. It is a free study space, a community hub and a lifeline for people without reliable internet.\n\nInvesting in them is one of the cheapest ways to widen opportunity.",
                false, 75, 80],
            ['international', 'analysis', $writer, 'published', 'How Global Supply Chains Shape Local Prices',
                'A delay at a distant port can change what you pay at a corner shop.',
                "Most products on a shelf travel through many countries before reaching a customer. A disruption anywhere along the route is felt at the till.\n\nUnderstanding this helps explain why prices can change even when nothing seems to have happened locally.",
                false, 110, 100],
            ['entertainment', 'feature', $writer, 'published', 'The Return of the Mixtape',
                'Curated playlists are the modern cassette, and people are sharing them again.',
                "Handing someone a mixtape was once an act of affection. Today, a shared playlist does the same job.\n\nThe format changed; the intention did not.",
                false, 60, 120],

            // ---- Articles in other workflow states (for testing the editorial flow) ----
            ['technology', 'review', $writer, 'pending_review', 'Three Budget Laptops for Students, Compared',
                'What you actually get for a student-friendly price.',
                "We compared three affordable laptops on keyboard quality, battery life and upgrade options.\n\nThe cheapest choice surprised us.",
                false, 0, 3],
            ['sports', 'opinion', $writer, 'draft', 'Why School Sports Still Matter',
                null,
                "School sports teach teamwork, discipline and how to lose gracefully. (Draft in progress.)",
                false, 0, 2],
            ['business', 'feature', $writer, 'revision_requested', 'The Rise of Home-Based Food Businesses',
                'Home kitchens are becoming small enterprises.',
                "Across many neighbourhoods, home cooks are turning family recipes into businesses.",
                false, 0, 8],
            ['international', 'opinion', $writer, 'approved', 'Learning Languages in the Age of Translation Apps',
                'Why speaking another language is still worth the effort.',
                "Translation apps are impressive, but they cannot replace the connection that comes from speaking someone's language.\n\nThey are a bridge, not a destination.",
                false, 0, 5],
        ];

        foreach ($rows as [$cat, $type, $author, $status, $title, $excerpt, $body, $featured, $views, $hoursAgo]) {
            $isPublished = $status === 'published';

            $article = Article::forceCreate([
                'user_id' => $author->id,
                'category_id' => $cats[$cat],
                'type' => ArticleType::from($type),
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $body,
                'status' => ArticleStatus::from($status),
                'is_featured' => $featured,
                'views' => $views,
                'submitted_at' => $status === 'draft' ? null : now()->subHours($hoursAgo + 12),
                'published_at' => $isPublished ? now()->subHours($hoursAgo) : null,
            ]);

            if ($status === 'revision_requested') {
                ArticleReview::create([
                    'article_id' => $article->id,
                    'actor_id' => $admin->id,
                    'from_status' => ArticleStatus::PendingReview,
                    'to_status' => ArticleStatus::RevisionRequested,
                    'comment' => 'Good topic. Please add one or two concrete examples and expand the conclusion.',
                ]);
            }
        }
    }
}