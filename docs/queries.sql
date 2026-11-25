-- =====================================================================
--  IronPulse Gym — example SQL queries (MySQL / MariaDB)
--  Hand-written equivalents of what the Laravel app does, for study.
--  All queries were run against the seeded demo database.
-- =====================================================================

-- 1) INNER JOIN (3 tables): every active subscription with member and plan
SELECT m.member_code, m.name AS member, p.name AS plan, s.start_date, s.end_date,
       DATEDIFF(s.end_date, CURDATE()) AS days_left
FROM subscriptions s
JOIN members m          ON m.id = s.member_id            -- FK subscriptions.member_id → members.id
JOIN membership_plans p ON p.id = s.membership_plan_id   -- FK subscriptions.membership_plan_id → membership_plans.id
WHERE s.status = 'active'
ORDER BY s.end_date
LIMIT 10;

-- 2) LEFT JOIN + GROUP BY: members per trainer (trainers with 0 members still appear)
SELECT t.name AS trainer, t.specialization, COUNT(m.id) AS members
FROM trainers t
LEFT JOIN members m ON m.trainer_id = t.id
GROUP BY t.id, t.name, t.specialization
ORDER BY members DESC;

-- 3) LEFT JOIN + GROUP BY + HAVING: subscriptions that still have money due
SELECT s.id AS subscription_id, m.name AS member, p.name AS plan,
       (s.amount - s.discount)                                    AS net_amount,
       COALESCE(SUM(pay.amount), 0)                               AS paid,
       (s.amount - s.discount) - COALESCE(SUM(pay.amount), 0)     AS due
FROM subscriptions s
JOIN members m          ON m.id = s.member_id
JOIN membership_plans p ON p.id = s.membership_plan_id
LEFT JOIN payments pay  ON pay.subscription_id = s.id
GROUP BY s.id, m.name, p.name, s.amount, s.discount
HAVING due > 0
ORDER BY due DESC;

-- 4) Anti-join (NOT EXISTS): active members with no running subscription → renewal list
SELECT m.member_code, m.name, m.phone
FROM members m
WHERE m.status = 'active'
  AND NOT EXISTS (SELECT 1 FROM subscriptions s
                  WHERE s.member_id = m.id AND s.status = 'active' AND s.end_date >= CURDATE());

-- 5) Aggregation by month: income vs expenses for the current year
SELECT mon.m AS month,
       COALESCE((SELECT SUM(amount) FROM payments WHERE YEAR(paid_at) = YEAR(CURDATE()) AND MONTH(paid_at) = mon.m), 0)  AS income,
       COALESCE((SELECT SUM(amount) FROM expenses WHERE YEAR(spent_on) = YEAR(CURDATE()) AND MONTH(spent_on) = mon.m), 0) AS expenses
FROM (SELECT 1 m UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
      UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) mon
ORDER BY mon.m;

-- 6) M:N through the associative table: how many distinct members bought each plan
SELECT p.name AS plan, COUNT(DISTINCT s.member_id) AS members, COUNT(s.id) AS subscriptions_sold,
       SUM(s.amount - s.discount) AS revenue
FROM membership_plans p
LEFT JOIN subscriptions s ON s.membership_plan_id = p.id
GROUP BY p.id, p.name
ORDER BY revenue DESC;

-- 7) Self-contained attendance report: visits per member in the last 30 days
SELECT m.name, COUNT(a.id) AS visits,
       ROUND(AVG(TIME_TO_SEC(TIMEDIFF(a.check_out, a.check_in))) / 60) AS avg_minutes
FROM members m
JOIN attendances a ON a.member_id = m.id
WHERE a.date >= CURDATE() - INTERVAL 30 DAY
GROUP BY m.id, m.name
ORDER BY visits DESC
LIMIT 10;

-- 8) Who collected the money: payments per staff user (LEFT JOIN keeps NULL received_by)
SELECT COALESCE(u.name, '(unknown)') AS received_by, COUNT(*) AS payments, SUM(pay.amount) AS total
FROM payments pay
LEFT JOIN users u ON u.id = pay.received_by
GROUP BY u.name;

-- =====================================================================
--  More join types, subqueries, set operations and functions
-- =====================================================================

-- 9) RIGHT JOIN: every plan, even ones nobody has bought (plans on the right)
SELECT p.name AS plan, COUNT(s.id) AS times_sold
FROM subscriptions s
RIGHT JOIN membership_plans p ON p.id = s.membership_plan_id
GROUP BY p.id, p.name;

-- 10) CROSS JOIN: every plan × every payment method (price list for the front desk)
SELECT p.name AS plan, m.method, p.price
FROM membership_plans p
CROSS JOIN (SELECT 'cash' AS method UNION SELECT 'card' UNION SELECT 'bank_transfer' UNION SELECT 'online') m
ORDER BY p.price, m.method;

-- 11) SELF JOIN: pairs of members who joined on the same day
SELECT a.name AS member_a, b.name AS member_b, a.joined_at
FROM members a
JOIN members b ON a.joined_at = b.joined_at AND a.id < b.id;

-- 12) FULL OUTER JOIN emulated with UNION (MySQL has no FULL JOIN):
--     all trainers and all members, matched where possible
SELECT t.name AS trainer, m.name AS member FROM trainers t LEFT JOIN members m ON m.trainer_id = t.id
UNION
SELECT t.name AS trainer, m.name AS member FROM trainers t RIGHT JOIN members m ON m.trainer_id = t.id;

-- 13) Single-row subquery: members heavier than the average weight
SELECT name, weight_kg FROM members
WHERE weight_kg > (SELECT AVG(weight_kg) FROM members)
ORDER BY weight_kg DESC;

-- 14) Multi-row subquery with IN: members who have ever bought the annual plan
SELECT member_code, name FROM members
WHERE id IN (SELECT member_id FROM subscriptions
             WHERE membership_plan_id = (SELECT id FROM membership_plans WHERE name = 'Annual Champion'));

-- 15) Correlated subquery: each member's most recent payment
SELECT m.name, pay.invoice_no, pay.amount, pay.paid_at
FROM members m
JOIN payments pay ON pay.member_id = m.id
WHERE pay.paid_at = (SELECT MAX(p2.paid_at) FROM payments p2 WHERE p2.member_id = m.id);

-- 16) EXISTS: trainers who have at least one suspended or inactive member
SELECT t.name FROM trainers t
WHERE EXISTS (SELECT 1 FROM members m WHERE m.trainer_id = t.id AND m.status <> 'active');

-- 17) Aggregates + DISTINCT + BETWEEN + LIKE
SELECT COUNT(*) AS payments, COUNT(DISTINCT member_id) AS paying_members,
       MIN(amount) AS smallest, MAX(amount) AS largest, ROUND(AVG(amount), 2) AS average, SUM(amount) AS total
FROM payments
WHERE paid_at BETWEEN '2026-01-01' AND '2026-12-31' AND invoice_no LIKE 'INV-2026%';

-- 18) String, date and math functions
SELECT UPPER(name) AS name_upper, CONCAT(member_code, ' / ', phone) AS contact,
       TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age,
       ROUND(weight_kg / POW(height_cm / 100, 2), 1) AS bmi
FROM members
ORDER BY bmi DESC
LIMIT 10;
