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
