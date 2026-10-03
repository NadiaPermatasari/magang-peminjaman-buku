<?php

namespace Database\Seeders;

use App\Enums\BookCondition;
use App\Enums\BookCopyStatus;
use App\Enums\MemberStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Member;
use App\Models\Rack;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Optional sample data for local development/demo (spec §54). Never runs
 * outside local/testing — see DatabaseSeeder.
 */
class DemoMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Book::query()->exists()) {
            return;
        }

        $categories = collect(['Fiksi', 'Non-Fiksi', 'Teknologi', 'Sejarah'])
            ->map(fn ($name) => Category::create(['name' => $name, 'slug' => Str::slug($name)]));

        $racks = collect([
            ['code' => 'A1', 'name' => 'Rak Fiksi A1', 'location' => 'Lantai 1'],
            ['code' => 'B1', 'name' => 'Rak Non-Fiksi B1', 'location' => 'Lantai 1'],
            ['code' => 'C1', 'name' => 'Rak Teknologi C1', 'location' => 'Lantai 2'],
        ])->map(fn ($rack) => Rack::create($rack));

        $titles = [
            'Bumi Manusia', 'Laskar Pelangi', 'Negeri 5 Menara', 'Supernova',
            'Filosofi Kopi', 'Sejarah Nusantara', 'Belajar Laravel', 'Clean Code',
        ];

        foreach ($titles as $i => $title) {
            $book = Book::create([
                'isbn' => '978'.random_int(1000000000, 9999999999),
                'title' => $title,
                'slug' => Str::slug($title),
                'category_id' => $categories->random()->id,
                'publication_year' => random_int(1990, 2024),
                'language' => 'Indonesia',
                'page_count' => random_int(120, 480),
                'description' => "Deskripsi singkat untuk buku {$title}.",
                'rack_id' => $racks->random()->id,
                'is_active' => true,
            ]);

            $copyCount = random_int(1, 3);
            for ($c = 1; $c <= $copyCount; $c++) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'barcode' => 'BC'.str_pad((string) (($i * 10) + $c), 6, '0', STR_PAD_LEFT),
                    'inventory_code' => 'INV-'.($i + 1).'-'.$c,
                    'acquisition_date' => now()->subDays(random_int(30, 900)),
                    'condition' => BookCondition::GOOD,
                    'status' => BookCopyStatus::AVAILABLE,
                ]);
            }
        }

        // Anggota demo lengkap dengan akun login, supaya alur peminjaman bisa
        // dicoba dari sisi peminjam tanpa perlu mengatur email server dulu.
        // Password diambil dari DEMO_MEMBER_PASSWORD bila diset.
        $password = env('DEMO_MEMBER_PASSWORD', 'Anggota123!');

        $members = [
            ['name' => 'Siti Aminah', 'email' => 'siti.aminah@example.test'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@example.test'],
            ['name' => 'Rina Wulandari', 'email' => 'rina.wulandari@example.test'],
        ];

        foreach ($members as $i => $member) {
            $user = User::create([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('anggota');

            $m = new Member([
                'user_id' => $user->id,
                'member_number' => 'M-DEMO'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'name' => $member['name'],
                'email' => $member['email'],
                'phone' => '08'.random_int(1000000000, 1999999999),
                'address' => 'Jl. Contoh No. '.($i + 1).', Jakarta',
                'status' => MemberStatus::ACTIVE,
                'joined_at' => now()->subMonths(random_int(1, 24)),
            ]);
            $m->setIdentityNumber((string) random_int(3170000000000000, 3179999999999999));
            $m->save();
        }

        $this->command?->info("Anggota demo dibuat (siti.aminah@example.test dll) dengan password: {$password}");
    }
}
