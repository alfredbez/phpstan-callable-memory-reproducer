# PHPStan memory spike from eager callable checks on two-element arrays

A minimal standalone reproducer for unexpected memory use in PHPStan during the analysis of two-element array constants.

## Problem

In PHP, a two-element string array (`['ClassName', 'methodName']`) is valid callable syntax.

When PHPStan creates a `ConstantArrayType` for any two-element array, `isCallable()` checks whether the array is a valid callable:
1. PHPStan checks if the first element matches a declared class name.
2. If it does, `BetterReflection` reflects that class to check whether the second element is an existing method.
3. If that class is large (for example, a compiled dependency injection container, or generated API models), PHPStan parses the entire class into memory.
4. This happens even when the array is a private constant used only for string comparisons (such as `in_array($value, self::ALLOWED, true)`), and is never passed to a callable argument.

```php
// Triggers reflection of BigContainer (~220 MB RAM):
private const ALLOWED = ['BigContainer', 'value'];

// Does not trigger reflection (~42 MB RAM):
private const ALLOWED = ['value', 'BigContainer'];
```

## Reproduction

### Requirements
- PHP 8.2 or newer
- Composer

### Steps

```bash
composer install
php generate_big_class.php
php run.php
```

`generate_big_class.php` creates a synthetic 2.2 MB class (`generated/BigContainer.php`) with 25,000 methods, simulating a large project class.

### Benchmark results

Running on PHP 8.3 via `php run.php`:

| Scenario | Consumed RAM | Duration |
| :--- | :--- | :--- |
| `src/FooClean.php` (first item is not a class name) | 42.68 MB | 0.10 s |
| `src/FooLeaking.php` (first item matches `BigContainer`) | 220.37 MB | 0.43 s |

Matching a declared class name at array index 0 causes five times more memory use and four times longer analysis time on a single file. With 20+ MB compiled container classes, the memory jump exceeds 1 GB per file.

## CI

A GitHub Actions workflow runs the benchmark on every push in `.github/workflows/reproducer.yml`.


## Proposed upstream fix

1. **Lazy callable checks:** Defer class reflection in `ConstantArrayType` until the array is evaluated in a context that requires a `callable` (such as a function parameter type-hinted as `callable`, `call_user_func`, or an `is_callable()` assertion).
2. **Avoid eager reflection on array constants:** Do not reflect classes referenced inside private or protected constants that have not been typed as callables.
