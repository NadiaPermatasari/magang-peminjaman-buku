<?php

namespace Tests\Feature;

use App\Actions\Loans\CreateLoan;
use App\Enums\MemberStatus;
use App\Exceptions\LoanException;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    /**
     * PHPUnit runs with CSRF verification bypassed by design (Laravel's
     * VerifyCsrfToken::runningUnitTests() short-circuit) — that's what lets
     * every other test in this suite POST/PUT without attaching a token.
     * So this doesn't trigger an actual 419 dynamically; instead it asserts
     * structurally that every state-changing route we registered still
     * goes through the 'web' middleware group, which is what applies
     * VerifyCsrfToken outside of tests (spec §29).
     */
    public function test_state_changing_routes_are_protected_by_the_web_csrf_middleware(): void
    {
        $routes = collect(['categories.store', 'loans.store', 'users.store', 'settings.update', 'fines.waive']);

        $routes->each(function (string $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Route [{$name}] is not registered.");
            $this->assertContains('web', $route->middleware(), "Route [{$name}] is missing the 'web' middleware group (CSRF).");
        });
    }

    public function test_mass_assignment_cannot_set_role_via_user_create_payload(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        $response = $this->actingAs($admin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post('/users', [
                'name' => 'Percobaan Eskalasi',
                'email' => 'escalate@example.test',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'super-admin',
                // Attempt to sneak in fields the controller never reads.
                'is_admin' => true,
                'permissions' => ['*'],
            ]);

        $response->assertRedirect(route('users.index'));

        $created = User::where('email', 'escalate@example.test')->firstOrFail();
        // Role assignment only happens through the explicit syncRoles()
        // call in UserController::store — 'is_admin'/'permissions' from the
        // raw payload were never read, so no direct permission was attached
        // on top of whatever the super-admin *role* itself grants.
        $this->assertTrue($created->hasRole('super-admin'));
        $this->assertCount(0, $created->getDirectPermissions());
    }

    public function test_invalid_file_upload_is_rejected_for_book_cover(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();

        $maliciousFile = UploadedFile::fake()->createWithContent('shell.php', '<?php echo "pwned"; ?>');

        $response = $this->actingAs($admin)->post('/books', [
            'title' => 'Judul Uji',
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'cover' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('cover');
    }

    public function test_inactive_member_cannot_submit_a_loan(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user, ['status' => MemberStatus::INACTIVE]);
        $book = $this->makeBookWithCopy();

        $this->expectException(LoanException::class);
        app(CreateLoan::class)->handle($member, [$book->id]);
    }

    public function test_suspended_member_cannot_submit_a_loan(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user, ['status' => MemberStatus::SUSPENDED]);
        $book = $this->makeBookWithCopy();

        $this->expectException(LoanException::class);
        app(CreateLoan::class)->handle($member, [$book->id]);
    }
}
