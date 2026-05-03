<?php
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use Brick\Money\Money;
use Illuminate\Support\Facades\Event;
use App\Enums\LedgerEntryType;

beforeEach(function () {
    $this->users = User::factory(10)->create();
    $this->self = $this->users[0];

    $this->group = Group::factory()
        ->withGroupUsers(5)
        ->create([
            'user_id' => $this->self->id,
        ]);

    $this->group_users = $this->group->groupUsers;
    $this->group_user = $this->group_users->where('user_id', $this->self->id)->first();

    $this->actingAs($this->self);
});

test('creating a standard debt creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    foreach ($debt->shares as $share) {
        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $debt->groupUser->user->id,
            'type' => LedgerEntryType::DEBT_OWNERSHIP_CREATED,
            'amount' => $share->amount->getMinorAmount()->toInt(),
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'type' => LedgerEntryType::SHARE_CREATED,
            'amount' => $share->amount->getMinorAmount()->negated(),
        ]);
    }
});