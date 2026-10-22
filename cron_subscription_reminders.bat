@echo off
REM =======================================================================
REM Yellow Markets - Subscription Expiry Reminders Daily Cron
REM Scheduled to run daily at 15:20 (3:20 PM)
REM Sends 30-day and 7-day prior expiration reminders to merchants without login
REM =======================================================================

cd /d "c:\xampp\htdocs\ymstore"
echo [%date% %time%] Running daily subscription expiry reminder cron... >> cron_subscription_reminders.log

if exist "c:\xampp\php\php.exe" (
    "c:\xampp\php\php.exe" index.php CronController subscription_expiry_reminders >> cron_subscription_reminders.log 2>&1
) else (
    php index.php CronController subscription_expiry_reminders >> cron_subscription_reminders.log 2>&1
)

echo [%date% %time%] Finished. >> cron_subscription_reminders.log

