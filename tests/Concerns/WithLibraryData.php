<?php

namespace Tests\Concerns;

use App\Enums\MemberStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

/**
 * Shared fixtures for feature tests: role/permission catalogue + quick
 * factories for the domain objects the loan flow depends on.
 */
trait WithLibraryData
{
    protected function seedRoles(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeUser(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));

        $user->assignRole($role);

        return $user;
    }

    protected function makeMember(?User $user = null, array $attributes = []): Member
    {
        $member = new Member(array_merge([
            'member_number' => 'M-'.Str::random(8),
            'name' => $user?->name ?? 'Anggota Test',
            'email' => $user?->email,
            'status' => MemberStatus::ACTIVE,
            'joined_at' => now(),
            'user_id' => $user?->id,
        ], $attributes));

        $member->setIdentityNumber(null);
        $member->save();

        return $member;
    }

    protected function makeBookWithCopy(array $bookAttributes = [], string $copyStatus = 'AVAILABLE'): Book
    {
        $book = Book::create(array_merge([
            'title' => 'Buku Test '.Str::random(6),
            'slug' => Str::slug('buku-test-'.Str::random(6)),
            'category_id' => Category::factory()->create()->id,
            'is_active' => true,
        ], $bookAttributes));

        BookCopy::create([
            'book_id' => $book->id,
            'barcode' => 'BC-'.Str::random(10),
            'condition' => 'GOOD',
            'status' => $copyStatus,
        ]);

        return $book->fresh('copies');
    }
}
