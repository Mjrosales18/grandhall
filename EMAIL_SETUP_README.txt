GRAND M.J ROSALES HORIZON - OPTIONAL CUSTOMER EMAIL NOTIFICATIONS

FILES INCLUDED IN THIS PATCH
- reserve.php: makes the customer email field optional.
- process_reservation.php: saves reservations with or without an email and sends the pending email when an email is supplied.
- admin_dashboard.php: sends an approval email only when a reservation changes from Pending to Approved.
- email_config.php: SMTP sender configuration.
- email_functions.php: responsive HTML email template and PHPMailer sending function.

INSTALLATION
1. Back up your current project first.
2. Extract these five PHP files into the root of your Grand_M.J_Rosales_Horizon project and overwrite the matching files.
3. Keep the existing PHPMailer directory and images/logo.png in place.
4. Open email_config.php and set SMTP_USERNAME, SMTP_PASSWORD, and SMTP_FROM_EMAIL.
5. For Gmail, use a Google App Password with 2-Step Verification enabled. Do not use your normal Gmail password. Keep email_config.php private and do not upload it to a public repository.
6. Confirm that your hosting/local PHP environment allows outbound SMTP on port 587.

IMPORTANT
- The existing reservations.email column is NOT NULL, but an empty string is accepted by the provided INSERT, so no SQL migration is needed for the supplied schema.
- Customers who leave Email Address blank can still submit reservations; no email is sent to them.
- SMTP credentials are intentionally placeholders. Emails will not send until you configure them.
- A failed email send is logged in the PHP error log and does not cancel a successfully saved reservation or an admin approval.
- The approval notification is sent only on the first Pending -> Approved transition, preventing duplicate approval emails on repeated clicks.
