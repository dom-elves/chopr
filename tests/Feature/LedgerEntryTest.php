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

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

test('updating a standard debt creates no ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    $original_values = $debt->shares->map(function($share) {
        return $share->amount->getMinorAmount()->toInt();
    });

    $debtService = app(DebtService::class);
    $debtService->updateDebt($debt, [
        'name' => $debt->name,
        'amount' => 2000,
    ]);

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

    $this->assertTrue(checkLedgerEntryTotals($debt));
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

/**
 * Split even debt tests. Same principles as standard debt,
 * though updating debt amounts will create ledger entries.
 */
test('creating a split even debt creates the correct ledger entries', function() {
        $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 1,
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

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

test('updating a split even debt creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 1,
    ]);

    $original_splits = $debt->amount->split($debt->shares->count());

    $debtService = app(DebtService::class);
    $debtService->updateDebt($debt, [
        'name' => $debt->name,
        'amount' => 2000,
    ]);

    $debt->refresh();

    foreach ($debt->shares as $key => $share) {
        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $debt->groupUser->user->id,
            'type' => LedgerEntryType::DEBT_OWNERSHIP_UPDATED,
            'amount' => $share->amount->minus($original_splits[$key])->getMinorAmount()->toInt(),
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'type' => LedgerEntryType::SHARE_UPDATED,
            'amount' => $share->amount->minus($original_splits[$key])->getMinorAmount()->negated()->toInt(),
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

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

test('deleting a split even debt creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 1,
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

        $this->assertEquals($share->groupUser->user->refresh()->balance->getMinorAmount()->toInt(), 0);
    }
});

/**
 * Standard share tests.
 * Similar to debt tests but only test balances of affected users.
 */
test('creating a standard share creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    $original_debt_owner_balance = $debt->groupUser->user->balance;
    $original_share_owner_balance = $debt->groupUsers->last()->user->balance;

    $share = Share::factory()->create([
        'debt_id' => $debt->id,
        'group_user_id' => $debt->groupUsers->last()->id,
        'amount' => 200,
    ]);

    $share->groupUser->refresh();

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

    $this->assertEquals(
        $this->group_user->user->balance,
        $original_debt_owner_balance->plus($share->amount)
    );

    $this->assertEquals(
        $share->groupUser->user->balance,
        $original_share_owner_balance->minus($share->amount)
    );

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

test('updating a standard share amount creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    // find an appropriate share first
    // store all original values for assertions later
    // then make the share amount dirty
    $share = $debt->shares->reject(fn($share) =>
        $share->group_user_id === $this->group_user->id)->first();

    $original_share_amount = $share->amount;
    $original_debt_owner_balance = $debt->groupUser->user->balance;
    $original_share_owner_balance = $share->groupUser->user->balance;

    $share->amount = $share->amount->plus(250);

    $shareService = app(ShareService::class);
    $shareService->updateShare($share);

    $share->groupUser->refresh();

    $this->assertDatabaseHas('ledger_entries', [
        'share_id' => $share->id,
        'user_id' => $debt->groupUser->user->id,
        'type' => LedgerEntryType::DEBT_OWNERSHIP_UPDATED,
        'amount' => $share->amount->minus($original_share_amount)->getMinorAmount()->toInt(),
    ]);

    $this->assertDatabaseHas('ledger_entries', [
        'share_id' => $share->id,
        'user_id' => $share->groupUser->user->id,
        'type' => LedgerEntryType::SHARE_UPDATED,
        'amount' => $share->amount->minus($original_share_amount)->getMinorAmount()->negated(),
    ]);

    $this->assertEquals(
        $this->group_user->user->balance,
        $original_debt_owner_balance->plus(250)
    );

    $this->assertEquals(
        $share->groupUser->user->balance,
        $original_share_owner_balance->minus(250)
    );

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

test('deleting a standard share creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    $share = $debt->shares->reject(fn($share) => $share->group_user_id === $this->group_user->id)->first();

    $original_debt_owner_balance = $debt->groupUser->user->balance;
    $original_share_owner_balance = $share->groupUser->user->balance;

    $shareService = app(ShareService::class);
    $shareService->deleteShare($share);

    $share->refresh();

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
        'amount' => $share->amount->getMinorAmount()->toInt(),
    ]);

    $this->assertEquals(
        $this->group_user->user->balance,
        $original_debt_owner_balance->minus($share->amount)
    );

    $this->assertEquals(
        $share->groupUser->user->balance,
        $original_share_owner_balance->plus($share->amount)
    );

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

/**
 * Split even shares tests.
 * Same as share tests, but deletion requires all user balance recalcs.
 */
test('creating a split share creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 1,
    ]);

    $original_debt = $debt;
    $original_splits = $debt->amount->split($debt->shares->count());

    $shareService = app(ShareService::class);
    $shareService->createSingleShare($debt, [
        'group_user_id' => $debt->groupUsers->last()->id,
        'name' => 'new name',
        'amount' => 1, // this value doesn't actually matter, it just needs to be something
        'currency' => 'GBP',
        'debt_id' => $debt->id,
    ]);

    $debt->refresh();
    
    $this->assertEquals(
        $original_debt->amount,
        $debt->amount
    );

    foreach ($debt->shares as $key => $share) {
        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $debt->groupUser->user->id,
            'type' => LedgerEntryType::DEBT_OWNERSHIP_UPDATED,
            'amount' => $share->amount->minus($original_splits[$key])->getMinorAmount()->toInt(),
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'type' => LedgerEntryType::SHARE_UPDATED,
            'amount' => $share->amount->minus($original_splits[$key])->getMinorAmount()->negated()->toInt(),
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
});

test('deleting a split share creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 1,
    ]);

    $original_splits = $debt->amount->split($debt->shares->count());

    $shareService = app(ShareService::class);
    $shareService->deleteShare($debt->shares->first());

    $debt->refresh();

    foreach ($debt->shares as $key => $share) {
        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $debt->groupUser->user->id,
            'type' => LedgerEntryType::DEBT_OWNERSHIP_UPDATED,
            'amount' => $share->amount->minus($original_splits[$key])->getMinorAmount()->toInt(),
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'share_id' => $share->id,
            'user_id' => $share->groupUser->user->id,
            'type' => LedgerEntryType::SHARE_UPDATED,
            'amount' => $share->amount->minus($original_splits[$key])->getMinorAmount()->negated()->toInt(),
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

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

/**
 * Test for toggling 'sent' status. Same concept as updating a share, 
 * agnostic to standard/split debts.
 */
test('sent being toggled creates the correct ledger entries', function() {
    $debt = Debt::factory()->withShares()->create([
        'group_user_id' => $this->group_user->id,
        'amount' => 1000,
        'split_even' => 0,
    ]);

    $original_debt_owner_balance = $debt->groupUser->user->balance;

    $share = $debt->shares->reject(fn($share) => $share->group_user_id === $this->group_user->id)->first();

    $original_share_owner_balance = $share->groupUser->user->balance;

    $original_share_amount = $share->amount;

    $shareService = app(ShareService::class);
    $shareService->updateSentStatus($share, !$share->sent);

    $share->groupUser->refresh();

    if ($share->sent) {
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
            'amount' => $share->amount->getMinorAmount()->toInt(),
        ]);

        $this->assertEquals(
            $this->group_user->user->balance,
            $original_debt_owner_balance->minus($share->amount)
        );

        $this->assertEquals(
            $share->groupUser->user->balance,
            $original_share_owner_balance->plus($share->amount)
        );
    } else {
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

        $this->assertEquals(
            $this->group_user->user->balance,
            $original_debt_owner_balance->plus($share->amount)
        );

        $this->assertEquals(
            $share->groupUser->user->balance,
            $original_share_owner_balance->minus($share->amount)
        );
    }

    $this->assertTrue(checkLedgerEntryTotals($debt));
});

function checkLedgerEntryTotals($debt): bool
{
    $total = $debt->ledgerEntries->reduce(function ($carry, LedgerEntry $ledger_entry) {
        if ($carry === null) {
            return $ledger_entry->amount;
        }

        return $carry->plus($ledger_entry->amount);
    });

    return $total->getMinorAmount()->toInt() ===  0;
}