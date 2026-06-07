# Excel Import Debug Spec

## Problem Description

User reports a **500 error** at the `complete_excel_import` step when importing items via Excel (Items → Import Items from Excel → Complete Import). The error occurs for **both new item imports and existing item updates**. The request **hangs** (shows a spinner indefinitely) with no useful error displayed to the user.

## Timeline

1. **Previous session**: Added duplicate barcode DB checking in `dedup_excel_import_data()` + language strings
2. **After those fixes**: The 500 error appeared at the `complete_excel_import` step
3. **User environment**: PHP 8.1+, MySQL, chrome. No access to error logs.

## Local Testing Findings (Laragon, PHP 8.x, MySQL 8)

### Issue 1: MySQL 8 `NO_AUTO_CREATE_USER` Crash
- **File**: `application/hooks/setup_phppos.php`
- **Error**: `SQLSTATE[42000]: Syntax error: Unknown system variable 'NO_AUTO_CREATE_USER'`
- **Cause**: MySQL 8.0.11+ removed the `NO_AUTO_CREATE_USER` sql_mode. The hook `setup_mysql()` blindly sets it on every page load.
- **Impact**: ALL pages return 500, not just import. Foundational blocker.
- **Fix**: Added MySQL version check — only set `NO_AUTO_CREATE_USER` if version < 8.0.11. Used `$CI->db->version()` instead of raw SQL query.

### Issue 2: MY_Controller Dynamic Property Deprecation (PHP 8.2+)
- **File**: `application/core/MY_Controller.php`
- **Error (suppressed on prod)**: `Creation of dynamic property MY_Controller::$load is deprecated`
- **Cause**: The lazy loading constructor sets `$this->load = ''` and iterates `is_loaded()` to pre-initialize properties before `parent::__construct()`. In PHP 8.2+, creating these dynamic properties emits deprecation notices. If `display_errors` is on, these corrupt the JSON response.
- **Impact**: On PHP 8.2+ with display_errors on, the JSON response is corrupted. On PHP 8.1+, this is not a problem (yet).
- **Current status**: The pre-initialization code is necessary for the lazy loading `__get()` method to work correctly during `parent::__construct()`. Removing it breaks the app. The `error_reporting(E_ALL & ~E_DEPRECATED)` fix was added temporarily during local testing but should be removed for production (it was reverted already).

### Issue 3: `strpos(null)` on PHP 8.1+ (MOST LIKELY ROOT CAUSE)
- **File**: `application/controllers/Items.php`, `complete_excel_import()` function
- **Code**: Line ~2240: `strpos($columns_with_data[$key]['data'][$i], '%')`
- **Cause**: When a commission column cell is empty/null in the spreadsheet, `$columns_with_data[$key]['data'][$i]` can be null (not a string). PHP 8.1+ made `strpos(null, ...)` throw a TypeError instead of silently coercing null to `""`.
- **Impact**: A spreadsheet with any empty commission cells would throw a 500 at `complete_excel_import`.
- **Fix**: Added `?? ''` null coalescing: `strpos(($columns_with_data[$key]['data'][$i] ?? ''), '%')`.

### Issue 4: CodeIgniter Core Deprecations (PHP 8.2+)
- **Various system files**: `system/database/drivers/mysqli/mysqli_driver.php` sets dynamic property `$autoinit`; `system/helpers/table_helper.php` uses `${var}` string interpolation syntax deprecated in PHP 8.2+.
- **Impact**: Emits deprecation notices. If `display_errors` is on, this corrupts JSON output.
- **Root cause of note**: `system/database/DB_driver.php` has `$this->autoinit` which gets set from config but isn't declared as a class property. In PHP 8.2+, this is deprecated.

## Import Flow (Step-by-Step)

```
Step 1: excel_import() — Loads view
Step 2: do_excel_upload() — Uploads file, stores in session
Step 3: do_excel_import_map() — Parses spreadsheet, stores column data + row count in session
Step 4: dedup_excel_import_data() — (AJAX) Validates duplicates: within-spreadsheet item numbers, product IDs, database barcodes
Step 5: complete_excel_import() — (AJAX) Runs the actual import loop
Step 6: get_import_errors() — Fetches error log from session (called after complete)
```

## Files Modified (All Sessions)

| File | Change |
|------|--------|
| `application/controllers/Items.php` | Added duplicate barcode DB check in `dedup_excel_import_data()` |
| `application/controllers/Items.php` | Added `?? ''` null-safe guard for `strpos()` in `complete_excel_import()` |
| `application/language/english/items_lang.php` | Added `items_duplicate_barcode_in_database` |
| `application/language/thai/items_lang.php` | Added `items_duplicate_barcode_in_database` |
| `application/hooks/setup_phppos.php` | Added MySQL version check before `NO_AUTO_CREATE_USER` sql_mode |
| `excel-import-debug-spec.md` | This file |

## Root Cause Likelihood (User's PHP 8.1+ Production)

| Hypothesis | Likelihood | Explanation |
|------------|------------|-------------|
| **H1: `strpos(null)`** | **HIGH** | Any spreadsheet with empty commission cells triggers TypeError on PHP 8.1+ |
| **H2: MySQL `NO_AUTO_CREATE_USER`** | **MEDIUM** | Only if user is on MySQL 8.0.11+. Would crash ALL pages, not just import. |
| **H3: Dynamic property deprecation** | **LOW** | Only on PHP 8.2+ with `display_errors=on`. User said 8.1+. |
| **H4: Database connection/timeout** | **LOW** | Only for very large spreadsheets with many rows. |
| **H5: Memory limit** | **LOW** | Only for very large spreadsheets. |

## Next Steps

1. **User should apply the fixes to their production server** (Files 1-4 above)
2. **User should revert `application/config/database.php`** to production credentials if it was changed
3. **If error persists**, the user should check the actual PHP version (`phpinfo()` or `php -v`) and provide the exact error message
4. **For PHP 8.2+**: Additional fixes may be needed for core CodeIgniter deprecations (MY_Controller dynamic properties, DB_driver $autoinit, table_helper ${var} syntax)

## How to Test

1. Create a test spreadsheet with 2-3 items:
   - One new item with a unique barcode
   - One item with an existing barcode (to test duplicate detection)
   - One item with empty commission cell and empty tier price cell
2. Upload and import via Items → Import Items from Excel
3. Verify: (a) Dedup catches existing barcodes, (b) Complete Import succeeds, (c) Errors show properly
