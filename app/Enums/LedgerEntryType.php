<?php

namespace App\Enums;

enum LedgerEntryType: string
{
    /**
     * For what would be auditing purposes, we need ledger entry types as enums.
     * Would essentially make them easier to query, organise, and test against.
     */
    case DEBT_OWNERSHIP_CREATED = 'debt_ownership_created';
    case DEBT_OWNERSHIP_UPDATED = 'debt_ownership_updated';
    case DEBT_OWNERSHIP_DELETED = 'debt_ownership_deleted';
    case SHARE_CREATED = 'share_created';
    case SHARE_UPDATED = 'share_updated';
    case SHARE_DELETED = 'share_deleted';
}
