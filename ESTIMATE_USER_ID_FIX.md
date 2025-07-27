# Estimate User ID Fix

## Problem

When creating estimates, the system was throwing a database error:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'user_id' in 'where clause'
```

This error occurred because the `User` model's `getMonthlyEstimateCount()` method was trying to query the `jdosa_estimates` table with a `user_id` column that didn't exist.

## Root Cause

The migration `m250725_000004_add_user_id_to_invoices_table.php` only added the `user_id` column to the `jdosa_invoices` table, but the `jdosa_estimates` table was missing this column. However, the user limit checking code was trying to count estimates by user_id.

## Solution

1. **Created Migration**: Added `migrations/m250726_000001_add_user_id_to_estimates_table.php` to add the `user_id` column to the estimates table.

2. **Updated Estimate Model**:

   - Added `user_id` property to the model documentation
   - Added `user_id` to validation rules
   - Added `user_id` to attribute labels
   - Added `getUser()` relation method
   - Updated `beforeSave()` to automatically set user_id when creating estimates

3. **Updated EstimateController**:

   - Set `user_id` when creating new estimates
   - Set `user_id` when duplicating estimates

4. **Updated User Model**:
   - Added backward compatibility check in `getMonthlyEstimateCount()` to handle cases where the column might not exist yet
   - Falls back to counting estimates through company ownership if user_id column is not available

## Files Modified

- `migrations/m250726_000001_add_user_id_to_estimates_table.php` (new)
- `models/Estimate.php`
- `controllers/EstimateController.php`
- `models/User.php`
- `fix_estimate_user_id.sh` (new)

## How to Apply the Fix

1. Run the migration on the server:

   ```bash
   ./yii migrate --interactive=0
   ```

   Or use the provided script:

   ```bash
   ./fix_estimate_user_id.sh
   ```

2. The fix includes backward compatibility, so existing estimates without user_id will still work.

3. New estimates will automatically have the user_id set to the current logged-in user.

## Verification

After applying the fix:

1. Try creating a new estimate - it should work without errors
2. Check that the estimate has the correct user_id in the database
3. Verify that user monthly limits are working correctly
