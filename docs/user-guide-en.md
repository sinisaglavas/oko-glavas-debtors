# Quick user guide

## Sign in

Sign in with your account. A vendor can enter debtors, debts, and payments. An administrator can also void a payment.

## Annual overview

On **Debtors by year**, choose a year, optionally enter a search term or status, and click **Apply**. The cards show the opening debt, total paid, and outstanding debt for the entire year. Click a debtor's name to open the detail page.

## Enter a debtor and debt

Click **New debtor / debt**. Select an existing person or enter a new person's details, then provide the date, total amount, and 2 to 24 installments. The application calculates the installment schedule and outstanding balance.

## Payments and voiding

On the debtor page, choose a debt, enter the amount, payment date, and optional note, then save the payment. A payment cannot exceed the outstanding balance. An administrator can void a payment by providing a required reason; the original remains in the history.

## Backups

Before production use, configure daily database backups and verify restoration in a separate development environment. Keep backups and the `.env` file outside the public directory with restricted access.
