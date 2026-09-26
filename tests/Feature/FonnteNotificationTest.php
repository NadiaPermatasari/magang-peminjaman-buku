<?php

namespace Tests\Feature;

use App\Actions\Loans\CreateLoan;
use App\Models\Setting;
use App\Notifications\LoanApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class FonnteNotificationTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_whatsapp_message_is_sent_when_enabled_and_configured(): void
    {
        $this->seedRoles();
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        config(['services.fonnte.token' => 'test-token']);
        Setting::set('whatsapp_notification_enabled', true);

        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user, ['phone' => '081234567890']);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $user->notify(new LoanApprovedNotification($loan));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request['target'] === '6281234567890'
                && str_contains($request['message'], 'Peminjaman disetujui')
                && $request->hasHeader('Authorization', 'test-token');
        });
    }

    public function test_whatsapp_message_is_not_sent_when_setting_disabled(): void
    {
        $this->seedRoles();
        Http::fake();
        config(['services.fonnte.token' => 'test-token']);
        Setting::set('whatsapp_notification_enabled', false);

        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user, ['phone' => '081234567890']);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $user->notify(new LoanApprovedNotification($loan));

        Http::assertNothingSent();
    }

    public function test_whatsapp_message_is_not_sent_when_token_not_configured(): void
    {
        $this->seedRoles();
        Http::fake();
        config(['services.fonnte.token' => null]);
        Setting::set('whatsapp_notification_enabled', true);

        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user, ['phone' => '081234567890']);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $user->notify(new LoanApprovedNotification($loan));

        Http::assertNothingSent();
    }

    public function test_whatsapp_message_is_not_sent_when_member_has_no_phone(): void
    {
        $this->seedRoles();
        Http::fake();
        config(['services.fonnte.token' => 'test-token']);
        Setting::set('whatsapp_notification_enabled', true);

        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user, ['phone' => null]);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $user->notify(new LoanApprovedNotification($loan));

        Http::assertNothingSent();
    }

    public function test_phone_number_is_normalized_to_62_prefix(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');
        $this->makeMember($user, ['phone' => '0812-3456-7890']);

        $this->assertSame('6281234567890', $user->fresh()->routeNotificationForFonnte());
    }

    public function test_phone_already_in_62_format_is_unchanged(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');
        $this->makeMember($user, ['phone' => '6281234567890']);

        $this->assertSame('6281234567890', $user->fresh()->routeNotificationForFonnte());
    }

    public function test_no_member_or_no_phone_returns_null(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');

        $this->assertNull($user->routeNotificationForFonnte());
    }
}
