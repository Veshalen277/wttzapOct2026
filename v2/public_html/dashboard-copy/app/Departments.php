<?php
namespace Portal;

/**
 * Department membership.
 *
 * Primary department  = users_tbl.user_scale (unchanged, still what the session carries in u_data[2]).
 * Extra departments   = rows in user_departments, honoured only for management roles.
 *
 * Every method degrades gracefully when the user_departments table has not been created yet:
 * the user simply has their primary department only, exactly as before.
 */
final class Departments
{
    /** Roles allowed to hold more than one department. Override with MULTI_DEPT_ROLES (e.g. "2,6,7"). */
    public static function eligibleRoles(): array
    {
        $raw = defined('MULTI_DEPT_ROLES') ? (string) MULTI_DEPT_ROLES : '0,2,3,4,5,6,7';
        $roles = [];
        foreach (explode(',', $raw) as $part) {
            $part = trim($part);
            if ($part !== '' && ctype_digit($part)) $roles[] = (int) $part;
        }
        return array_values(array_unique($roles));
    }

    public static function roleIsEligible(int $role): bool
    {
        return in_array($role, self::eligibleRoles(), true);
    }

    /** Every department code in use (primary or extra), sorted, never empty strings. */
    public static function all(\mysqli $con): array
    {
        $codes = [];
        try {
            $res = $con->query("SELECT DISTINCT TRIM(user_scale) AS d FROM users_tbl WHERE user_scale <> ''");
            while ($row = $res->fetch_assoc()) $codes[$row['d']] = true;
        } catch (\Throwable $e) {
            error_log('Departments::all (users_tbl): ' . $e->getMessage());
        }
        try {
            $res = $con->query("SELECT DISTINCT TRIM(dept_code) AS d FROM user_departments WHERE dept_code <> ''");
            while ($row = $res->fetch_assoc()) $codes[$row['d']] = true;
        } catch (\Throwable $e) {
            // table not created yet: fine
        }
        $list = array_keys($codes);
        sort($list, SORT_NATURAL | SORT_FLAG_CASE);
        return $list;
    }

    /** @return array{primary:?string, role:?int} */
    private static function identity(\mysqli $con, int $userId): array
    {
        try {
            $st = $con->prepare('SELECT TRIM(user_scale) AS d, user_role FROM users_tbl WHERE id = ?');
            $st->bind_param('i', $userId);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            $st->close();
            if ($row) return ['primary' => $row['d'] !== '' ? $row['d'] : null, 'role' => (int) $row['user_role']];
        } catch (\Throwable $e) {
            error_log('Departments::identity: ' . $e->getMessage());
        }
        return ['primary' => null, 'role' => null];
    }

    /** Extra departments as stored (does not check role eligibility). */
    public static function extrasFor(\mysqli $con, int $userId): array
    {
        try {
            $st = $con->prepare('SELECT TRIM(dept_code) AS d FROM user_departments WHERE user_id = ? ORDER BY dept_code');
            $st->bind_param('i', $userId);
            $st->execute();
            $out = array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'd');
            $st->close();
            return $out;
        } catch (\Throwable $e) {
            return []; // table missing or unreadable: no extras
        }
    }

    /**
     * Departments this user may act in: primary first, then extras (extras only if the
     * user's CURRENT role is eligible, so demoting someone removes the extra access).
     */
    public static function forUser(\mysqli $con, int $userId): array
    {
        $id = self::identity($con, $userId);
        $list = $id['primary'] !== null ? [$id['primary']] : [];
        if ($id['role'] !== null && self::roleIsEligible($id['role'])) {
            foreach (self::extrasFor($con, $userId) as $d) {
                if (!in_array(strtolower($d), array_map('strtolower', $list), true)) $list[] = $d;
            }
        }
        return $list;
    }

    public static function has(\mysqli $con, int $userId, string $dept): bool
    {
        $dept = strtolower(trim($dept));
        foreach (self::forUser($con, $userId) as $d) {
            if (strtolower($d) === $dept) return true;
        }
        return false;
    }

    /**
     * Department to attribute an action to. Empty request => primary department.
     * A requested department must be one the user really holds (checked in the database,
     * never trusted from the browser). Returns the canonical code.
     *
     * @throws \InvalidArgumentException
     */
    public static function resolve(\mysqli $con, int $userId, $requested): string
    {
        $allowed = self::forUser($con, $userId);
        if (!$allowed) throw new \InvalidArgumentException('Your employee profile has no department.');
        $requested = is_string($requested) ? trim($requested) : '';
        if ($requested === '') return $allowed[0];
        foreach ($allowed as $d) {
            if (strcasecmp($d, $requested) === 0) return $d;
        }
        throw new \InvalidArgumentException('You are not assigned to that department.');
    }

    /**
     * Replace a user's extra departments. Primary department is never stored as an extra.
     * Non-eligible roles end up with none. Unknown codes are rejected.
     *
     * @return string[] the extras now stored
     * @throws \InvalidArgumentException|\Throwable
     */
    public static function replaceExtras(\mysqli $con, int $userId, array $codes, int $grantedBy): array
    {
        $id = self::identity($con, $userId);
        if ($id['role'] === null) throw new \InvalidArgumentException('That user does not exist.');

        $known = array_map('strtolower', self::all($con));
        $primaryLower = strtolower((string) $id['primary']);
        $clean = [];
        foreach ($codes as $code) {
            $code = trim((string) $code);
            if ($code === '' || strlen($code) > 50) continue;
            if (!in_array(strtolower($code), $known, true)) {
                throw new \InvalidArgumentException('Unknown department: ' . $code);
            }
            if (strtolower($code) === $primaryLower) continue;
            $clean[strtolower($code)] = $code;
        }
        if (!self::roleIsEligible($id['role'])) $clean = [];

        $con->begin_transaction();
        try {
            $del = $con->prepare('DELETE FROM user_departments WHERE user_id = ?');
            $del->bind_param('i', $userId);
            $del->execute();
            $del->close();
            if ($clean) {
                $ins = $con->prepare('INSERT INTO user_departments (user_id, dept_code, granted_by) VALUES (?, ?, ?)');
                foreach ($clean as $code) {
                    $ins->bind_param('isi', $userId, $code, $grantedBy);
                    $ins->execute();
                }
                $ins->close();
            }
            $con->commit();
        } catch (\Throwable $e) {
            @$con->rollback();
            throw $e;
        }
        return array_values($clean);
    }
}
