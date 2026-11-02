GRAND M.J ROSALES HORIZON - RECOMMENDED FEATURES

NEW FEATURES
1. Admin Blocked Dates: administrators can block dates for maintenance, holidays, or private venue use.
2. Public Availability Calendar: blocked dates appear as unavailable and cannot be selected.
3. Duplicate Booking Protection: pending and approved reservations are limited to one active reservation per event date. Rejected reservations do not hold a date.
4. Server-side Validation: date, email, guest count, payment method, add-ons and uploaded image types/sizes are validated on submission.
5. Mobile-friendly admin controls: the new blocked-date page uses the responsive site stylesheet.

INSTALLATION
A. If using the EXISTING database:
   1. Back up function_hall_db first in phpMyAdmin (Export).
   2. Select function_hall_db in phpMyAdmin.
   3. Open the Import tab and import feature_update.sql ONCE.
   4. Replace your project files with this updated project folder.

B. If creating a NEW database:
   1. Create database function_hall_db.
   2. Import the updated function_hall_db.sql.
   3. Do NOT run feature_update.sql as well; the full SQL already contains the new schema.
   4. Copy the project folder into htdocs.

ADMIN USE
Log in to the admin portal and click “Block Dates”. Choose a date, optionally enter a reason, then click “Block Date”. Use “Unblock” to make it bookable again.

IMPORTANT
The generated active_event_date unique index is the database-level protection against two pending/approved reservations using the same date. If the database already contains duplicate active dates, resolve those duplicates before importing feature_update.sql.
