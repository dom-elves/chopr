<?php
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\LedgerEntry;
use Brick\Money\Money;
use Illuminate\Support\Facades\Event;
use App\Enums\LedgerEntryType;
use App\Services\DebtService;
use App\Services\ShareService;

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

/**
 * Standard Debt tests. Each test will cover correct ledger entries being created,
 * and user balances being correctly adjusted. Tests based around adding debts,
 * will cover the ledger entries correctly summing up 0 as each share,
 * has a +/- ledger.
 */

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

        if ($share->group_user_id === $debt->group_user_id) {
            $this->assertEquals(
                $share->groupUser->user->balance,
                $debt->amount->minus($share->amount)
            );
        } else {
            $this->assertEquals(
                $share->groupUser->user->balance->getMinorAmount()->toInt(),
                $share->amount->getMinorAmount()->negated()->toInt()
            );
        }
    }

    $total = $debt->ledgerEntries->reduce(function ($carry, LedgerEntry $ledger_entry) {
        if ($carry === null) {
            return $ledger_entry->amount;
        }

        return $carry->plus($ledger_entry->amount);
    });

    $this->assertEquals($total->getMinorAmount()->toInt(), 0);
});

test('updating a standard debt creates no ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    $debtService = app(DebtService::class);
    $debtService->updateDebt($debt, [
        'name' => $debt->name,
        'amount' => 2000,
    ]);

    $original_values = $debt->shares->map(function($share) {
        return $share->amount->getMinorAmount()->toInt();
    });

    // as this can read a little strange;
    // get the ledger entries for each debt share
    // count & assert that they are 2x the share count
    // asserting that no extras were created
    $this->assertEquals(
        LedgerEntry::whereIn('share_id', $debt->shares->pluck('id')
            ->toArray())
        ->get()
        ->count(),
        $debt->shares->count() * 2
    );

    foreach ($debt->shares as $key => $share) {
        $this->assertEquals(
            $share->amount->getMinorAmount()->toInt(),
            $original_values[$key]
        );
    }
});

test('deleting a standard debt creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    $debtService = app(DebtService::class);
    $debtService->deleteDebt($debt);

    foreach ($debt->shares as $share) {
        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $debt->groupUser->user->id,
            'type' => LedgerEntryType::DEBT_OWNERSHIP_DELETED,
            'amount' => $share->amount->getMinorAmount()->negated(),
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'type' => LedgerEntryType::SHARE_DELETED,
            'amount' => $share->amount->getMinorAmount(),
        ]);

        $this->assertEquals($share->groupUser->user->refresh()->balance->getMinorAmount()->negated()->toInt(), 0);
    }
});