@echo off
REM =======================================================================
REM Yellow Markets - Subscription Expiry Reminders Daily Cron
REM Scheduled to run daily at 15:20 (3:20 PM)
REM Sends 30-day and 7-day prior expiration reminders to merchants without login
REM =======================================================================

cd /d "c:\xampp\htdocs\ymstore"
php index.php CronController subscription_expiry_reminders
