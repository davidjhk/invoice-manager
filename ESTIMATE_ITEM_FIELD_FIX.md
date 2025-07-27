# Estimate Item Field Name Fix

## Problem

When creating estimates, the system was throwing an error:

```
Setting unknown property: app\models\EstimateItem::unit_price
```

## Root Cause

The `EstimateController` was trying to set a `unit_price` property on `EstimateItem` objects, but the actual field name in the `EstimateItem` model is `rate`, not `unit_price`.

## Solution

Updated the `EstimateController` to use the correct field name `rate` instead of `unit_price` in the following methods:

1. **actionCreate()** - When creating new estimate items
2. **actionUpdate()** - When updating estimate items
3. **actionDuplicate()** - When copying estimate items
4. **actionConvertToInvoice()** - When converting estimate items to invoice items

The fix includes backward compatibility by checking for both `rate` and `unit_price` in the form data:

```php
$item->rate = $itemData['rate'] ?? ($itemData['unit_price'] ?? 0);
```

## Files Modified

- `controllers/EstimateController.php`

## Verification

After applying the fix:

1. Try creating a new estimate with items - it should work without errors
2. Try updating an existing estimate - it should work without errors
3. Try duplicating an estimate - it should work without errors
4. Try converting an estimate to invoice - it should work without errors
