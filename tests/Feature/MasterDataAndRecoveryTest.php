<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::create(['role_name' => 'Waka/Admin']);
    $this->guruRole = Role::firstOrCreate(['role_name' => 'Guru']);
    $this->admin = User::create([
        'username' => 'waka',
        'password' => 'password123',
        'fullname' => 'Waka Sekolah',
        'role_id' => $this->adminRole->id,
    ]);
    $this->guru = User::create([
        'username' => 'guru',
        'email' => 'guru@example.test',
        'password' => 'password123',
        'fullname' => 'Guru Satu',
        'role_id' => $this->guruRole->id,
    ]);
});

test('admin can create and list member accounts without exposing passwords', function () {
    $this->actingAs($this->admin)
        ->post(route('master-data.store'), [
            'fullname' => 'Guru Baru',
            'username' => 'guru.baru',
            'email' => 'guru.baru@example.test',
            'phone_number' => '6281234567890',
            'role_id' => $this->guruRole->id,
            'password' => 'MemberPass123',
            'password_confirmation' => 'MemberPass123',
        ])
        ->assertRedirect(route('master-data.index'));

    $member = User::where('username', 'guru.baru')->firstOrFail();

    expect(Hash::check('MemberPass123', $member->password))->toBeTrue();

    $this->get(route('master-data.index'))
        ->assertOk()
        ->assertSee('href="'.route('master-data.index').'"', false)
        ->assertSeeText('Guru Baru')
        ->assertSeeText('guru.baru')
        ->assertSee('value="guru.baru@example.test"', false)
        ->assertDontSee('MemberPass123');
});

test('only waka can access master data', function () {
    $this->actingAs($this->guru)
        ->get(route('master-data.index'))
        ->assertForbidden();
});

test('admin cannot create another admin through member management', function () {
    $this->actingAs($this->admin)
        ->post(route('master-data.store'), [
            'fullname' => 'Admin Tambahan',
            'username' => 'admin.baru',
            'email' => 'admin.baru@example.test',
            'role_id' => $this->adminRole->id,
            'password' => 'MemberPass123',
            'password_confirmation' => 'MemberPass123',
        ])
        ->assertSessionHasErrors('role_id');

    $this->assertDatabaseMissing('users', ['username' => 'admin.baru']);
});

test('admin can update a member email and reset the member password', function () {
    $this->actingAs($this->admin)
        ->patch(route('master-data.email', $this->guru), ['email' => 'new.guru@example.test'])
        ->assertRedirect();

    $this->actingAs($this->admin)
        ->patch(route('master-data.password', $this->guru), [
            'password' => 'ChangedPass123',
            'password_confirmation' => 'ChangedPass123',
        ])
        ->assertRedirect();

    expect($this->guru->fresh()->email)->toBe('new.guru@example.test')
        ->and(Hash::check('ChangedPass123', $this->guru->fresh()->password))->toBeTrue();
});

test('admin can change a member role between teacher and organization', function () {
    $organizationRole = Role::firstOrCreate(['role_name' => 'Organisasi']);

    $this->actingAs($this->admin)
        ->patch(route('master-data.role', $this->guru), ['role_id' => $organizationRole->id])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $this->guru->id,
        'role_id' => $organizationRole->id,
    ]);

    $this->get(route('master-data.index'))
        ->assertOk()
        ->assertSeeText('Organisasi');
});

test('user can reset password through emailed link', function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'unknown@example.test'])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertNothingSent();

    $this->post(route('password.email'), ['email' => $this->guru->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    $token = null;

    Notification::assertSentTo($this->guru, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
        $token = $notification->token;

        return true;
    });

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $this->guru->email,
        'password' => 'ResetPass123',
        'password_confirmation' => 'ResetPass123',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('status');

    expect(Hash::check('ResetPass123', $this->guru->fresh()->password))->toBeTrue();
});

test('guest can open password recovery and reset forms', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('password.request'));

    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('name="email"', false);

    $this->get(route('password.reset', ['token' => 'sample-token', 'email' => $this->guru->email]))
        ->assertOk()
        ->assertSee('name="password_confirmation"', false);
});

test('login form provides an accessible password visibility toggle', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('src="'.asset('images/logo-smkn-1-bantul.png').'"', false)
        ->assertSeeText('RAPI-Sekolah')
        ->assertSee('id="login-password" type="password"', false)
        ->assertSee('id="toggle-password"', false)
        ->assertSee('aria-label="Lihat password"', false)
        ->assertSee('passwordInput.type = isPasswordHidden ? \'text\' : \'password\';', false);
});

test('login shows the requested message when credentials are incorrect', function () {
    $this->from(route('login'))
        ->followingRedirects()
        ->post(route('login.submit'), [
            'username' => $this->guru->username,
            'password' => 'password-salah',
        ])
        ->assertOk()
        ->assertSeeText('Password salah bos.');
});

test('user can reset password using 6-digit otp', function () {
    $this->post(route('password.email'), ['email' => $this->guru->email])
        ->assertRedirect(route('password.reset', ['email' => $this->guru->email]))
        ->assertSessionHas('status');

    $otpRecord = DB::table('password_reset_tokens')
        ->where('email', $this->guru->email)
        ->first();

    expect($otpRecord)->not->toBeNull()
        ->and($otpRecord->otp)->toMatch('/^\d{6}$/');

    $this->post(route('password.update'), [
        'token' => $otpRecord->otp,
        'email' => $this->guru->email,
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Kata sandi berhasil diatur ulang. Silakan masuk.');

    expect(Hash::check('NewPassword123', $this->guru->fresh()->password))->toBeTrue();
});
