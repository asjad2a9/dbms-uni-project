-- =====================================================================
--  IronPulse Gym — DBMS objects (MySQL 8.0.16+ / MariaDB 10.3+)
--  CHECK constraints · extra indexes · audit table · 5 views ·
--  5 stored procedures · 3 triggers · DDL demonstration
--
--  Run ONCE, after `php artisan migrate --seed`:
--    phpMyAdmin → select the database → SQL tab → paste this file → Go
--    or:  mysql -u USER -p DB_NAME < docs/dbms_objects.sql
--  The Laravel app works with or without these objects.
--  Note: on some shared hosts CREATE TRIGGER / PROCEDURE needs the
--  TRIGGER and CREATE ROUTINE privileges (cPanel "ALL PRIVILEGES" includes them).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 0) DDL demonstration (temporary table: CREATE, ALTER, RENAME, TRUNCATE, DROP)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS ddl_demo, ddl_demo_renamed;
CREATE TABLE ddl_demo (id INT PRIMARY KEY, note VARCHAR(50));
ALTER TABLE ddl_demo ADD COLUMN created_on DATE;
ALTER TABLE ddl_demo MODIFY note VARCHAR(100) NOT NULL;
RENAME TABLE ddl_demo TO ddl_demo_renamed;
INSERT INTO ddl_demo_renamed VALUES (1, 'temporary row', CURDATE());
TRUNCATE TABLE ddl_demo_renamed;
DROP TABLE ddl_demo_renamed;

-- ---------------------------------------------------------------------
-- 1) CHECK constraints (domain integrity, matching the app's validation)
-- ---------------------------------------------------------------------
ALTER TABLE membership_plans
    ADD CONSTRAINT chk_plan_price    CHECK (price >= 0),
    ADD CONSTRAINT chk_plan_duration CHECK (duration_days > 0);
ALTER TABLE subscriptions
    ADD CONSTRAINT chk_sub_dates     CHECK (end_date >= start_date),
    ADD CONSTRAINT chk_sub_discount  CHECK (discount >= 0 AND discount <= amount);
ALTER TABLE payments
    ADD CONSTRAINT chk_payment_amount CHECK (amount > 0);
ALTER TABLE expenses
    ADD CONSTRAINT chk_expense_amount CHECK (amount >= 0);

-- ---------------------------------------------------------------------
-- 2) Extra indexes for common searches
-- ---------------------------------------------------------------------
CREATE INDEX idx_members_name   ON members (name);
CREATE INDEX idx_members_status ON members (status);

-- ---------------------------------------------------------------------
-- 3) Audit table (filled by trigger trg_subscriptions_au)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscription_audit_log (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id BIGINT UNSIGNED NOT NULL,
    old_status      VARCHAR(20),
    new_status      VARCHAR(20),
    old_end_date    DATE,
    new_end_date    DATE,
    changed_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_subscription FOREIGN KEY (subscription_id)
        REFERENCES subscriptions (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4) Views
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW v_active_memberships AS
SELECT m.id AS member_id, m.member_code, m.name AS member, m.phone,
       p.name AS plan, s.start_date, s.end_date,
       DATEDIFF(s.end_date, CURDATE()) AS days_left,
       t.name AS trainer
FROM subscriptions s
JOIN members m          ON m.id = s.member_id
JOIN membership_plans p ON p.id = s.membership_plan_id
LEFT JOIN trainers t    ON t.id = m.trainer_id
WHERE s.status = 'active' AND s.end_date >= CURDATE();

CREATE OR REPLACE VIEW v_subscription_balance AS
SELECT s.id AS subscription_id, m.member_code, m.name AS member, p.name AS plan,
       (s.amount - s.discount)                                AS net_amount,
       COALESCE(SUM(pay.amount), 0)                           AS paid,
       (s.amount - s.discount) - COALESCE(SUM(pay.amount), 0) AS due,
       s.status
FROM subscriptions s
JOIN members m          ON m.id = s.member_id
JOIN membership_plans p ON p.id = s.membership_plan_id
LEFT JOIN payments pay  ON pay.subscription_id = s.id
GROUP BY s.id, m.member_code, m.name, p.name, s.amount, s.discount, s.status;

CREATE OR REPLACE VIEW v_monthly_finance AS
SELECT month, SUM(income) AS income, SUM(expense) AS expenses, SUM(income) - SUM(expense) AS profit
FROM (
    SELECT DATE_FORMAT(paid_at,  '%Y-%m') AS month, amount AS income, 0 AS expense FROM payments
    UNION ALL
    SELECT DATE_FORMAT(spent_on, '%Y-%m') AS month, 0 AS income, amount AS expense FROM expenses
) x
GROUP BY month;

CREATE OR REPLACE VIEW v_trainer_workload AS
SELECT t.id AS trainer_id, t.name AS trainer, t.specialization,
       COUNT(m.id)                     AS assigned_members,
       SUM(m.status = 'active')        AS active_members
FROM trainers t
LEFT JOIN members m ON m.trainer_id = t.id
GROUP BY t.id, t.name, t.specialization;

CREATE OR REPLACE VIEW v_daily_attendance AS
SELECT a.date, COUNT(*) AS visits, COUNT(DISTINCT a.member_id) AS members,
       ROUND(AVG(TIME_TO_SEC(TIMEDIFF(a.check_out, a.check_in))) / 60) AS avg_minutes
FROM attendances a
GROUP BY a.date;

-- ---------------------------------------------------------------------
-- 5) Stored procedures
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_member_history;
DROP PROCEDURE IF EXISTS sp_expire_subscriptions;
DROP PROCEDURE IF EXISTS sp_expiring_soon;
DROP PROCEDURE IF EXISTS sp_revenue_by_method;
DROP PROCEDURE IF EXISTS sp_renew_subscription;

DELIMITER $$

-- Full subscription history of one member, with paid and due amounts
CREATE PROCEDURE sp_member_history(IN p_member_id BIGINT UNSIGNED)
BEGIN
    SELECT s.id, p.name AS plan, s.start_date, s.end_date, s.status,
           (s.amount - s.discount) AS net_amount,
           COALESCE((SELECT SUM(amount) FROM payments WHERE subscription_id = s.id), 0) AS paid
    FROM subscriptions s
    JOIN membership_plans p ON p.id = s.membership_plan_id
    WHERE s.member_id = p_member_id
    ORDER BY s.start_date DESC;
END$$

-- Mark past-due active subscriptions as expired; returns how many changed
CREATE PROCEDURE sp_expire_subscriptions(OUT p_expired INT)
BEGIN
    UPDATE subscriptions SET status = 'expired', updated_at = NOW()
    WHERE status = 'active' AND end_date < CURDATE();
    SET p_expired = ROW_COUNT();
END$$

-- Active subscriptions ending within the next p_days days (renewal call list)
CREATE PROCEDURE sp_expiring_soon(IN p_days INT)
BEGIN
    SELECT * FROM v_active_memberships
    WHERE days_left BETWEEN 0 AND p_days
    ORDER BY days_left;
END$$

-- Income per payment method between two dates
CREATE PROCEDURE sp_revenue_by_method(IN p_from DATE, IN p_to DATE)
BEGIN
    SELECT method, COUNT(*) AS payments, SUM(amount) AS total
    FROM payments
    WHERE paid_at BETWEEN p_from AND p_to
    GROUP BY method
    ORDER BY total DESC;
END$$

-- Renew a membership: insert subscription + payment in ONE transaction
CREATE PROCEDURE sp_renew_subscription(
    IN p_member_id BIGINT UNSIGNED, IN p_plan_id BIGINT UNSIGNED,
    IN p_start DATE, IN p_paid DECIMAL(10,2), IN p_method VARCHAR(20))
BEGIN
    DECLARE v_price DECIMAL(10,2);
    DECLARE v_days INT;
    DECLARE v_sub_id BIGINT UNSIGNED;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
        SELECT price, duration_days INTO v_price, v_days
        FROM membership_plans WHERE id = p_plan_id AND is_active = 1;
        IF v_price IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Plan not found or inactive';
        END IF;

        INSERT INTO subscriptions (member_id, membership_plan_id, start_date, end_date, amount, discount, status, created_at, updated_at)
        VALUES (p_member_id, p_plan_id, p_start, DATE_ADD(p_start, INTERVAL v_days DAY), v_price, 0, 'active', NOW(), NOW());
        SET v_sub_id = LAST_INSERT_ID();

        IF p_paid > 0 THEN
            INSERT INTO payments (invoice_no, member_id, subscription_id, amount, method, paid_at, created_at, updated_at)
            VALUES (CONCAT('INV-', DATE_FORMAT(CURDATE(), '%Y%m'), '-',
                           LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM payments p2), 5, '0')),
                    p_member_id, v_sub_id, p_paid, p_method, CURDATE(), NOW(), NOW());
        END IF;
    COMMIT;
    SELECT v_sub_id AS new_subscription_id;
END$$

-- ---------------------------------------------------------------------
-- 6) Triggers
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_payments_bi$$
DROP TRIGGER IF EXISTS trg_attendances_bi$$
DROP TRIGGER IF EXISTS trg_subscriptions_au$$

-- A payment linked to a subscription must belong to the same member
CREATE TRIGGER trg_payments_bi BEFORE INSERT ON payments
FOR EACH ROW
BEGIN
    IF NEW.subscription_id IS NOT NULL AND
       (SELECT member_id FROM subscriptions WHERE id = NEW.subscription_id) <> NEW.member_id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Subscription does not belong to this member';
    END IF;
END$$

-- Block a second check-in while the member is still inside the gym
CREATE TRIGGER trg_attendances_bi BEFORE INSERT ON attendances
FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM attendances
               WHERE member_id = NEW.member_id AND date = NEW.date AND check_out IS NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Member is already checked in';
    END IF;
END$$

-- Keep a history of status and end-date changes
CREATE TRIGGER trg_subscriptions_au AFTER UPDATE ON subscriptions
FOR EACH ROW
BEGIN
    IF NOT (OLD.status <=> NEW.status) OR NOT (OLD.end_date <=> NEW.end_date) THEN
        INSERT INTO subscription_audit_log (subscription_id, old_status, new_status, old_end_date, new_end_date)
        VALUES (NEW.id, OLD.status, NEW.status, OLD.end_date, NEW.end_date);
    END IF;
END$$

DELIMITER ;

-- ---------------------------------------------------------------------
-- 7) Try them
-- ---------------------------------------------------------------------
-- SELECT * FROM v_active_memberships ORDER BY days_left LIMIT 10;
-- SELECT * FROM v_subscription_balance WHERE due > 0;
-- SELECT * FROM v_monthly_finance ORDER BY month;
-- SELECT * FROM v_trainer_workload;
-- SELECT * FROM v_daily_attendance ORDER BY date DESC LIMIT 7;
-- CALL sp_member_history(1);
-- CALL sp_expire_subscriptions(@n); SELECT @n AS expired_now;
-- CALL sp_expiring_soon(7);
-- CALL sp_revenue_by_method('2026-01-01', '2026-12-31');
-- CALL sp_renew_subscription(1, 2, CURDATE(), 8000, 'cash');
-- SELECT * FROM subscription_audit_log ORDER BY id DESC;
