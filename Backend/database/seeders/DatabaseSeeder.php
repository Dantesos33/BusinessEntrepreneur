<?php

namespace Database\Seeders;

use App\Models\CollaborationRequest;
use App\Models\EntrepreneurDetail;
use App\Models\InvestorDetail;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Recreates the same demo dataset that was hardcoded as mock data in
 * the original React app (src/data/users.ts, messages.ts,
 * collaborationRequests.ts) as real rows in the database.
 *
 * Every seeded user's password is: password123
 *
 * Run with: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $entrepreneurs = [
            ['name' => 'Sarah Johnson', 'email' => 'sarah@techwave.io', 'bio' => 'Serial entrepreneur with 10+ years of experience in SaaS and fintech.', 'startup_name' => 'TechWave AI', 'pitch_summary' => 'AI-powered financial analytics platform helping SMBs make data-driven decisions.', 'funding_needed' => '$1.5M', 'industry' => 'FinTech', 'location' => 'San Francisco, CA', 'founded_year' => 2021, 'team_size' => 12, 'avatar' => 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg'],
            ['name' => 'David Chen', 'email' => 'david@greenlife.co', 'bio' => 'Environmental scientist turned entrepreneur. Passionate about sustainable solutions.', 'startup_name' => 'GreenLife Solutions', 'pitch_summary' => 'Biodegradable packaging alternatives for consumer goods and food industry.', 'funding_needed' => '$2M', 'industry' => 'CleanTech', 'location' => 'Portland, OR', 'founded_year' => 2020, 'team_size' => 8, 'avatar' => 'https://images.pexels.com/photos/614810/pexels-photo-614810.jpeg'],
            ['name' => 'Maya Patel', 'email' => 'maya@healthpulse.com', 'bio' => 'Former healthcare professional with an MBA. Building tech to improve patient care.', 'startup_name' => 'HealthPulse', 'pitch_summary' => 'Mobile platform connecting patients with mental health professionals in real-time.', 'funding_needed' => '$800K', 'industry' => 'HealthTech', 'location' => 'Boston, MA', 'founded_year' => 2022, 'team_size' => 5, 'avatar' => 'https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg'],
            ['name' => 'James Wilson', 'email' => 'james@urbanfarm.io', 'bio' => 'Agricultural engineer focused on urban farming solutions and food security.', 'startup_name' => 'UrbanFarm', 'pitch_summary' => 'IoT-enabled vertical farming systems for urban environments and food deserts.', 'funding_needed' => '$3M', 'industry' => 'AgTech', 'location' => 'Chicago, IL', 'founded_year' => 2019, 'team_size' => 14, 'avatar' => 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg'],
        ];

        $investors = [
            ['name' => 'Michael Rodriguez', 'email' => 'michael@vcinnovate.com', 'bio' => 'Early-stage investor with focus on B2B SaaS and fintech. Previously founded and exited two startups.', 'interests' => ['FinTech', 'SaaS', 'AI/ML'], 'stages' => ['Seed', 'Series A'], 'portfolio' => ['PayStream', 'DataSense', 'CloudSecure'], 'total' => 12, 'min' => '$250K', 'max' => '$1.5M', 'avatar' => 'https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg'],
            ['name' => 'Jennifer Lee', 'email' => 'jennifer@impactvc.org', 'bio' => 'Impact investor focused on climate tech, sustainable agriculture, and clean energy.', 'interests' => ['CleanTech', 'AgTech', 'Sustainability'], 'stages' => ['Seed', 'Series A', 'Series B'], 'portfolio' => ['SolarFlow', 'EcoPackage', 'CleanWater Solutions'], 'total' => 18, 'min' => '$500K', 'max' => '$3M', 'avatar' => 'https://images.pexels.com/photos/1181686/pexels-photo-1181686.jpeg'],
            ['name' => 'Robert Torres', 'email' => 'robert@healthventures.com', 'bio' => 'Healthcare-focused investor with medical background. Looking for innovations in patient care and biotech.', 'interests' => ['HealthTech', 'BioTech', 'Medical Devices'], 'stages' => ['Series A', 'Series B'], 'portfolio' => ['MediTrack', 'BioGenics', 'Patient+'], 'total' => 9, 'min' => '$1M', 'max' => '$5M', 'avatar' => 'https://images.pexels.com/photos/834863/pexels-photo-834863.jpeg'],
        ];

        $entrepreneurUsers = [];
        foreach ($entrepreneurs as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'entrepreneur',
                'avatar_url' => $data['avatar'],
                'bio' => $data['bio'],
                'is_online' => true,
                'email_verified_at' => now(),
            ]);

            EntrepreneurDetail::create([
                'user_id' => $user->id,
                'startup_name' => $data['startup_name'],
                'pitch_summary' => $data['pitch_summary'],
                'funding_needed' => $data['funding_needed'],
                'industry' => $data['industry'],
                'location' => $data['location'],
                'founded_year' => $data['founded_year'],
                'team_size' => $data['team_size'],
            ]);

            $entrepreneurUsers[] = $user;
        }

        $investorUsers = [];
        foreach ($investors as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'investor',
                'avatar_url' => $data['avatar'],
                'bio' => $data['bio'],
                'is_online' => true,
                'email_verified_at' => now(),
            ]);

            InvestorDetail::create([
                'user_id' => $user->id,
                'investment_interests' => $data['interests'],
                'investment_stage' => $data['stages'],
                'portfolio_companies' => $data['portfolio'],
                'total_investments' => $data['total'],
                'minimum_investment' => $data['min'],
                'maximum_investment' => $data['max'],
            ]);

            $investorUsers[] = $user;
        }

        CollaborationRequest::create([
            'investor_id' => $investorUsers[0]->id,
            'entrepreneur_id' => $entrepreneurUsers[0]->id,
            'message' => "I'd like to explore potential investment in {$entrepreneurUsers[0]->entrepreneurDetails->startup_name}. Your platform aligns well with my investment thesis.",
            'status' => 'pending',
        ]);

        CollaborationRequest::create([
            'investor_id' => $investorUsers[1]->id,
            'entrepreneur_id' => $entrepreneurUsers[1]->id,
            'message' => 'Your biodegradable packaging solutions align with my focus on sustainable investments. Let\'s discuss scaling possibilities.',
            'status' => 'accepted',
        ]);

        Message::create([
            'sender_id' => $entrepreneurUsers[0]->id,
            'receiver_id' => $investorUsers[0]->id,
            'content' => "Thanks for connecting. I'd love to discuss how our platform can help.",
            'is_read' => true,
        ]);

        Message::create([
            'sender_id' => $investorUsers[0]->id,
            'receiver_id' => $entrepreneurUsers[0]->id,
            'content' => "I'm interested in learning more. Are you available for a call this week?",
            'is_read' => false,
        ]);

        $this->command->info('Seeded '.count($entrepreneurUsers).' entrepreneurs and '.count($investorUsers).' investors.');
        $this->command->info('All demo accounts use the password: password123');
    }
}
