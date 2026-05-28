<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Seed realistic example data for emergency alerts, blackouts, broadcasts,
     * and the student messenger — used purely for visualization/demo purposes.
     */
    public function run(): void
    {
        // Resolve student IDs from what the StudentSeeder created
        $hazel = DB::table('students')->where('student_id', 'STU2026009')->first();
        $john  = DB::table('students')->where('student_id', 'STU2027001')->first();
        $jane  = DB::table('students')->where('student_id', 'STU2028005')->first();
        $mike  = DB::table('students')->where('student_id', 'STU2026012')->first();

        // Guard: skip if students haven't been seeded yet
        if (! $hazel || ! $john || ! $jane || ! $mike) {
            $this->command->warn('NotificationSeeder: Students not found. Run StudentSeeder first.');
            return;
        }

        // ─────────────────────────────────────────────────────────────
        // 1. SOS ALERTS
        // ─────────────────────────────────────────────────────────────

        // SOS #1 – Active / Help Needed (Jane Smith, Class 2028)
        DB::table('notifications')->insert([
            'student_id'   => $jane->id,
            'class'        => '2028',
            'type'         => 'sos',
            'sender_type'  => 'student',
            'message'      => 'HELP! I think someone is following me near the back gate. I am scared!',
            'latitude'     => 14.59685,
            'longitude'    => 120.98421,
            'battery_level'=> 12,
            'signal_status'=> 'Weak',
            'location'     => 'Back Gate, PNU Campus',
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => false,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subMinutes(8),
            'updated_at'   => Carbon::now()->subMinutes(8),
        ]);

        // SOS #2 – Acknowledged / Still Active (John Doe, Class 2027)
        DB::table('notifications')->insert([
            'student_id'   => $john->id,
            'class'        => '2027',
            'type'         => 'sos',
            'sender_type'  => 'student',
            'message'      => 'I fell near the covered court. My ankle hurts and I cannot walk. Please send help!',
            'latitude'     => 14.59712,
            'longitude'    => 120.98378,
            'battery_level'=> 45,
            'signal_status'=> 'Strong',
            'location'     => 'Covered Court, PNU Campus',
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,  // Admin already acknowledged
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subMinutes(25),
            'updated_at'   => Carbon::now()->subMinutes(20),
        ]);

        // SOS #3 – Resolved / Safe (Hazel Fernandez, Class 2026)
        DB::table('notifications')->insert([
            'student_id'   => $hazel->id,
            'class'        => '2026',
            'type'         => 'sos',
            'sender_type'  => 'student',
            'message'      => 'There was a fire alarm at the library. I panicked and sent SOS. I am okay now.',
            'latitude'     => 14.59654,
            'longitude'    => 120.98301,
            'battery_level'=> 85,
            'signal_status'=> 'Strong',
            'location'     => 'Main Library, PNU Campus',
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,
            'status'       => 'resolved',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subHours(2),
            'updated_at'   => Carbon::now()->subHours(1)->subMinutes(45),
        ]);

        // ─────────────────────────────────────────────────────────────
        // 2. BLACKOUT ALERTS
        // ─────────────────────────────────────────────────────────────

        // Blackout #1 – Jane Smith (Critical Battery)
        DB::table('notifications')->insert([
            'student_id'   => $jane->id,
            'class'        => '2028',
            'type'         => 'blackout',
            'sender_type'  => 'student',
            'message'      => 'My battery is critically low. Running low on battery, might lose connection soon.',
            'latitude'     => 14.59688,
            'longitude'    => 120.98415,
            'battery_level'=> 8,
            'signal_status'=> 'Weak',
            'location'     => 'Main Gate, PNU Campus',
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => false,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subMinutes(5),
            'updated_at'   => Carbon::now()->subMinutes(5),
        ]);

        // Blackout #2 – John Doe (Low Battery, acknowledged)
        DB::table('notifications')->insert([
            'student_id'   => $john->id,
            'class'        => '2027',
            'type'         => 'blackout',
            'sender_type'  => 'student',
            'message'      => 'Device is low on battery. Signal is dropping near the main gate area.',
            'latitude'     => 14.59700,
            'longitude'    => 120.98355,
            'battery_level'=> 15,
            'signal_status'=> 'Moderate',
            'location'     => 'Gate 2, PNU Campus',
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subHours(1),
            'updated_at'   => Carbon::now()->subHours(1),
        ]);

        // Blackout #3 – Mike Ross (Resolved)
        DB::table('notifications')->insert([
            'student_id'   => $mike->id,
            'class'        => '2026',
            'type'         => 'blackout',
            'sender_type'  => 'student',
            'message'      => 'Battery at 10%. Charger is already plugged in. Will be back online shortly.',
            'latitude'     => 14.59635,
            'longitude'    => 120.98290,
            'battery_level'=> 10,
            'signal_status'=> 'Strong',
            'location'     => 'Admin Building, PNU Campus',
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,
            'status'       => 'resolved',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subHours(3),
            'updated_at'   => Carbon::now()->subHours(2)->subMinutes(30),
        ]);

        // ─────────────────────────────────────────────────────────────
        // 3. BROADCAST NOTIFICATIONS (Admin → All / Per Class)
        // ─────────────────────────────────────────────────────────────

        // Broadcast #1 – All Students
        DB::table('notifications')->insert([
            'student_id'   => null,
            'class'        => 'all',
            'type'         => 'broadcast',
            'sender_type'  => 'admin',
            'subject'      => '🚨 Campus Lockdown Drill',
            'message'      => '<h4>🚨 Campus Lockdown Drill – April 28, 2026</h4><p>All students are reminded that a <strong>campus-wide lockdown drill</strong> will be conducted today, <strong>April 28, 2026 at 2:00 PM</strong>. Please proceed to your designated safe areas immediately when the alarm sounds. Do not use elevators. Stay calm and await further instructions from your assigned faculty.</p>',
            'latitude'     => null,
            'longitude'    => null,
            'battery_level'=> null,
            'signal_status'=> null,
            'location'     => null,
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subHours(4),
            'updated_at'   => Carbon::now()->subHours(4),
        ]);

        // Broadcast #2 – Class 2026 Only
        DB::table('notifications')->insert([
            'student_id'   => null,
            'class'        => '2026',
            'type'         => 'broadcast',
            'sender_type'  => 'admin',
            'subject'      => '📋 OJT Clearance Requirements',
            'message'      => '<h4>📋 Class 2026 – OJT Clearance Requirements</h4><p>Dear Class 2026, please submit your <strong>OJT clearance documents</strong> to the Registrar\'s Office no later than <strong>May 5, 2026</strong>. Required: Medical Certificate, Barangay Clearance, Parent\'s Consent Form, and 2x2 ID photos. Incomplete submissions will not be processed.</p>',
            'latitude'     => null,
            'longitude'    => null,
            'battery_level'=> null,
            'signal_status'=> null,
            'location'     => null,
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subHours(6),
            'updated_at'   => Carbon::now()->subHours(6),
        ]);

        // Broadcast #3 – Class 2027
        DB::table('notifications')->insert([
            'student_id'   => null,
            'class'        => '2027',
            'type'         => 'broadcast',
            'sender_type'  => 'admin',
            'subject'      => '📚 Midterm Examination Schedule',
            'message'      => '<h4>📚 Midterm Examination Schedule – Class 2027</h4><p>The <strong>Midterm Examination</strong> for Class 2027 is scheduled for <strong>May 10–14, 2026</strong>. Examinees must present their school ID and exam permit. No permit, no exam policy is strictly enforced. Review sessions will be held on May 7 and 8 in Room 301.</p>',
            'latitude'     => null,
            'longitude'    => null,
            'battery_level'=> null,
            'signal_status'=> null,
            'location'     => null,
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subDay(),
            'updated_at'   => Carbon::now()->subDay(),
        ]);

        // Broadcast #4 – All Students (Recent)
        DB::table('notifications')->insert([
            'student_id'   => null,
            'class'        => 'all',
            'type'         => 'broadcast',
            'sender_type'  => 'admin',
            'subject'      => '⚠️ G-Track App Update Required',
            'message'      => '<h4>⚠️ Important: G-Track App Update Required</h4><p>All students must update their <strong>G-Track mobile app</strong> to version <strong>2.1.0</strong> by <strong>April 30, 2026</strong>. The update includes critical improvements to SOS alert delivery and GPS accuracy. Students running outdated versions will not be visible on the live tracking dashboard.</p>',
            'latitude'     => null,
            'longitude'    => null,
            'battery_level'=> null,
            'signal_status'=> null,
            'location'     => null,
            'media_url'    => null,
            'video_url'    => null,
            'audio_url'    => null,
            'read'         => true,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subMinutes(45),
            'updated_at'   => Carbon::now()->subMinutes(45),
        ]);

        // ─────────────────────────────────────────────────────────────
        // 4. STUDENT MESSAGES & ADMIN REPLIES (Messenger Tab)
        // ─────────────────────────────────────────────────────────────

        // Conversation with Hazel
        $hazelMsg1Id = DB::table('notifications')->insertGetId([
            'student_id'   => $hazel->id,
            'class'        => '2026',
            'type'         => 'student_message',
            'sender_type'  => 'student',
            'message'      => 'Good morning po! Puwede po bang malaman ang schedule ng clearance signing?',
            'read'         => true,
            'status'       => 'replied',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subHours(5),
            'updated_at'   => Carbon::now()->subHours(5),
        ]);
        DB::table('notifications')->insert([
            'student_id'   => $hazel->id,
            'class'        => '2026',
            'type'         => 'admin_reply',
            'sender_type'  => 'admin',
            'message'      => 'Good morning, Hazel! The clearance signing is scheduled every Tuesday and Thursday, 8AM–12NN at the Registrar\'s Office. Please bring your complete requirements.',
            'read'         => false,
            'status'       => 'replied',
            'reply_to_id'    => $hazelMsg1Id,
            'created_at'   => Carbon::now()->subHours(4)->subMinutes(50),
            'updated_at'   => Carbon::now()->subHours(4)->subMinutes(50),
        ]);
        DB::table('notifications')->insert([
            'student_id'   => $hazel->id,
            'class'        => '2026',
            'type'         => 'student_message',
            'sender_type'  => 'student',
            'message'      => 'Thank you po! Dala ko na po yung lahat ng requirements. Magreregister na po ako bukas.',
            'read'         => true,
            'status'       => 'replied',
            'reply_to_id'    => $hazelMsg1Id,
            'created_at'   => Carbon::now()->subHours(4)->subMinutes(30),
            'updated_at'   => Carbon::now()->subHours(4)->subMinutes(30),
        ]);

        // Conversation with John
        $johnMsg1Id = DB::table('notifications')->insertGetId([
            'student_id'   => $john->id,
            'class'        => '2027',
            'type'         => 'student_message',
            'sender_type'  => 'student',
            'message'      => 'Sir/Ma\'am, the G-Track app keeps crashing when I try to send my location update. Is there a fix?',
            'read'         => true,
            'status'       => 'replied',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subHours(2),
            'updated_at'   => Carbon::now()->subHours(2),
        ]);
        DB::table('notifications')->insert([
            'student_id'   => $john->id,
            'class'        => '2027',
            'type'         => 'admin_reply',
            'sender_type'  => 'admin',
            'message'      => 'Hi John! Please try clearing the app cache and updating to the latest version (v2.1.0). If the issue persists, please visit the IT Office at Room 105 during office hours.',
            'read'         => false,
            'status'       => 'replied',
            'reply_to_id'    => $johnMsg1Id,
            'created_at'   => Carbon::now()->subHours(1)->subMinutes(50),
            'updated_at'   => Carbon::now()->subHours(1)->subMinutes(50),
        ]);

        // Unread message from Jane (no reply yet)
        DB::table('notifications')->insert([
            'student_id'   => $jane->id,
            'class'        => '2028',
            'type'         => 'student_message',
            'sender_type'  => 'student',
            'message'      => 'Ma\'am I need help. My classmates said my GPS is always wrong on the map. Can admin check?',
            'read'         => false,
            'status'       => 'pending',
            'reply_to_id'    => null,
            'created_at'   => Carbon::now()->subMinutes(15),
            'updated_at'   => Carbon::now()->subMinutes(15),
        ]);

        $this->command->info('✅ NotificationSeeder: Sample data created successfully!');
        $this->command->info('   → 3 SOS Alerts (1 active, 1 acknowledged, 1 resolved)');
        $this->command->info('   → 3 Blackout Alerts (1 active, 1 acknowledged, 1 resolved)');
        $this->command->info('   → 4 Broadcast Notifications (2 all-class, 1 Class 2026, 1 Class 2027)');
        $this->command->info('   → Student messages & admin replies for Hazel, John, and Jane');
    }
}
