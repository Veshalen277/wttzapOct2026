-- Extra departments for management users.
-- A user's PRIMARY department stays in users_tbl.user_scale (nothing existing changes).
-- Rows here are ADDITIONAL departments. They only take effect for management roles
-- (see MULTI_DEPT_ROLES in config.php); for other roles they are ignored.
--
-- Back up first, then run once (phpMyAdmin > SQL tab). Safe to re-run.

CREATE TABLE IF NOT EXISTS `user_departments` (
  `user_id`    int(11)     NOT NULL,
  `dept_code`  varchar(50) NOT NULL,
  `granted_by` int(11)     DEFAULT NULL,
  `granted_at` datetime    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `dept_code`),
  KEY `idx_user_departments_dept` (`dept_code`),
  CONSTRAINT `fk_user_departments_user`
    FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
