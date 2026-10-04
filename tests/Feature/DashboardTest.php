<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

/**
 * Kartu ringkasan dashboard adalah jalan pintas navigasi: tiap kartu harus
 * jadi tautan (bukan sekadar angka), dan satu metrik hanya boleh muncul
 * sekali walau user memegang izin petugas sekaligus admin.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_member_cards_are_clickable(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');
        $this->makeMember($user);

        $response = $this->actingAs($user)->get('/dashboard')->assertOk();

        foreach ([
            'Sedang Dipinjam' => '/loans?status=BORROWED',
            'Pengajuan Pending' => '/loans?status=PENDING',
            'Hampir Jatuh Tempo' => '/loans?status=BORROWED',
            'Terlambat' => '/loans?status=OVERDUE',
            'Perpanjangan Menunggu' => '/loan-extensions',
        ] as $label => $target) {
            $response->assertSee($label);
            $this->assertStringContainsString(
                'href="'.url($target).'"',
                $response->getContent(),
                "Kartu \"{$label}\" tidak tertaut ke {$target}."
            );
        }

        $this->assertSame(5, $this->countStatCards($response->getContent()));
    }

    public function test_staff_cards_are_clickable_and_not_duplicated(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');

        $response = $this->actingAs($user)->get('/dashboard')->assertOk();
        $html = $response->getContent();

        foreach ([
            'Menunggu Verifikasi' => '/loans/pending',
            'Terlambat' => '/loans/overdue',
            'Siap Diambil' => '/loans/ready',
            'Dikembalikan Hari Ini' => '/loans?status=RETURNED',
            'Jatuh Tempo Hari Ini' => '/loans/active',
            'Perpanjangan Menunggu' => '/loan-extensions',
            'Total Anggota' => '/members',
            'Peminjaman Aktif' => '/loans/active',
            'Pengajuan Pending' => '/loans?status=PENDING',
        ] as $label => $target) {
            $this->assertStringContainsString(
                'href="'.url($target).'"',
                $html,
                "Kartu \"{$label}\" tidak tertaut ke {$target}."
            );
        }

        // Dulu petugas dan admin punya dua blok kartu terpisah, jadi
        // super-admin melihat metrik yang sama dua kali.
        $this->assertSame(1, substr_count($html, '>Perpanjangan Menunggu</p>'));
        $this->assertSame(1, substr_count($html, '>Terlambat</p>'));
        $this->assertSame(9, $this->countStatCards($html));
    }

    /** Setiap kartu statistik adalah satu <a> dengan href. */
    private function countStatCards(string $html): int
    {
        return preg_match_all('/<a\s+href="[^"]+"\s+class="relative flex items-center/', $html);
    }
}
