# Implementation plan for optical shop debtor management

Status: implementation plan; business features have not been implemented yet.

Serbian version: [Plan izrade](plan-izrade-sr.md).

## 1. Goal and first-release scope

A simple internal application for recording debtors, reviewing debts by year, recording payments, and automatically calculating outstanding balances. The interface will be in Serbian and usable on desktop and mobile.

The first release includes:

- Employee login and logout, without public registration.
- Creating and editing first name, last name, address, town/city, and phone number.
- Recording the original debt amount, debt date, and 2–24 installments.
- An annual debtor overview with search and repayment status.
- A dedicated debtor page containing debts, payments, and outstanding balances.
- Recording payments and voiding incorrect payments while preserving history.
- Total original debt, total paid, and total outstanding for the selected year.

## 2. Working assumptions

These are proposed implementation rules, rather than additional user requirements:

- One optical shop and one currency, RSD by default; no interest or penalties.
- A debt belongs to the year in which it originated, regardless of payment dates.
- The same person may have multiple debts in the same or different years without duplicating personal details.
- Installments are equal as far as rounding permits; the final installment absorbs the difference. Payments may cover part of an installment or multiple installments.
- The number of payments is not the number of completed installments. Repayment progress is based on amounts.
- “Single page” means a dedicated page containing all debtor details. A separate SPA architecture is unnecessary for the first release.
- Automatic due dates, overdue tracking, SMS messages, receipt printing, exports, and cash-register integration are deferred to a later phase.

## 3. Screens and workflow

### Annual overview

The header contains a year selector (current year by default), a “New debtor / debt” button, and three summary cards: “Original debt”, “Paid”, and “Outstanding”. Previous years containing debts are available. A year without records displays zero totals and an empty-state message.

The table shows name, town/city, phone, total original debt for the selected year, paid amount, outstanding amount, and status. A person with multiple debts in that year appears once with aggregated amounts. Search covers first name, last name, and phone; a status filter distinguishes active and settled debts. The list is paginated. Summary cards always represent the entire selected year, independently of search and pagination.

### Creating a debtor and debt

The employee selects an existing person or creates a new one. First name, last name, address, town/city, and phone are required. A debt requires its date, a positive amount, and an integer installment count from 2 to 24. Its year is displayed from the date; older debts can be entered. Phone numbers are stored as text, preserving `+` and leading zeros.

Before saving, display the total debt and indicative installment amounts. Matching names and phone numbers trigger a possible-duplicate warning, but a name or shared family phone alone must not automatically prevent entry.

### Debtor page

Display contact details, the person's total outstanding balance across all years, and debts grouped by year. Opening the page from the annual overview selects that year, with annual and all-year totals clearly labeled.

For each debt, display its original amount, installment count, indicative installment amounts, total paid, outstanding balance, and “Active” or “Settled” status. Provide a “Record payment” form with amount, payment date, and optional note. The debt receiving the payment must be unambiguously selected.

Payment history shows the date, amount, note, employee who recorded it, and any void status. Refresh balances immediately after saving. Voiding requires confirmation and a reason; the original payment remains visible.

## 4. Data model

| Entity | Main fields and rules |
| --- | --- |
| `users` | Existing Laravel users; access restricted to employees. |
| `debtors` | `id`, `first_name`, `last_name`, `address`, `city`, `phone`, timestamps. |
| `debts` | `id`, `debtor_id`, `incurred_on`, `original_amount_minor`, `installment_count`, `created_by`, timestamps. Year is derived from `incurred_on`. |
| `payments` | `id`, `debt_id`, `amount_minor`, `paid_on`, `note`, `created_by`, unique `submission_token`, `voided_at`, `voided_by`, `void_reason`, timestamps. |

A debtor has many debts; a debt has many payments. Store money as integers in the smallest currency unit (para for RSD), without `float` arithmetic. Index foreign keys and the debt date. Filter annual queries using date ranges.

Derive balances and status from original amounts and valid payments instead of maintaining independently editable values. A separate installment table is unnecessary in the first release because installment amounts are derived from the debt amount and installment count.

## 5. Calculations and integrity rules

- Paid = sum of all non-voided payments for a particular debt.
- Outstanding = original debt amount − paid.
- A debt is settled when its outstanding balance is exactly zero.
- Annual original debt = sum of original amounts of debts originating in that year.
- Annual paid = sum of valid payments toward those debts, including payments made in later years.
- Annual outstanding = annual original debt − annual paid. This is the current balance of debts from that year, not a historical December 31 snapshot.
- The first N−1 installments receive the integer quotient of the total minor-unit amount divided by N; the final installment receives the remaining amount.
- Payments must be positive and cannot exceed the outstanding balance. Payment dates cannot precede the debt or be in the future; debt dates cannot be in the future.
- Saving a payment and rechecking the balance occur in one transaction with a lock on the debt. A unique token prevents duplicate posting of the same form submission.
- Voiding uses the same concurrency protection and reopens the debt if its balance becomes positive. A payment may be voided only once.
- Lock the debt amount, date, and installment count after the first recorded payment, even if that payment is later voided. Contact details remain editable. Subsequent debt corrections require a separate future workflow with history.
- Do not provide permanent deletion of debtors with debts or debts with payment history in the first release.

Example: a 2026 debt is RSD 24,000 in 12 installments of RSD 2,000. After payments of RSD 2,000 and RSD 3,000, the paid amount is RSD 5,000 and the outstanding balance is RSD 19,000. If the second payment arrives in 2027, it still reduces the balance of the 2026 debt. RSD 10,000 divided into 3 installments produces RSD 3,333.33 + 3,333.33 + 3,333.34.

## 6. Technical approach

Keep the existing Laravel project, Blade views, Tailwind CSS, and Vite. Use server-rendered forms and standard controllers, adding JavaScript only to improve input and presentation. The plan does not require a separate frontend framework.

Use migrations and Eloquent models for data, Form Request classes for validation, and a service for payment recording, voiding, and calculations. Plan for MySQL in shared production use, with transactions and row locking; perform final concurrent-payment checks against the same database engine. Select the development database during preparation.

Protect all business pages with login, server-side authorization, and CSRF protection. Employees may share the same permissions in the first release. Store passwords through Laravel's password mechanism and avoid unnecessary personal data in logs. Production uses HTTPS, daily database backups, and a verified restoration procedure.

## 7. Implementation sequence

1. **Preparation:** confirm working rules; configure the database, Serbian interface, currency, and Europe/Belgrade timezone; implement login and create an employee account. Outcome: protected application access.
2. **Data and calculations:** migrations, models, relationships, validation, payment and voiding service, factories, and sample data. Outcome: verifiable calculations and data integrity protection.
3. **Annual overview and entry:** year selector, summary cards, search, filters, pagination, and forms. Outcome: person and debt entry with correct annual grouping.
4. **Debtor page:** contact details, debts, installments, history, payment entry, and voiding. Outcome: a complete employee daily workflow.
5. **Verification and delivery:** automated calculation and access tests, manual mobile and desktop checks, production build, backup setup, and a short user guide. Outcome: an application ready for real data entry.

## 8. Acceptance criteria

- All required details and debts with 2 and 24 installments can be saved; 1, 25, and fractional installment counts are rejected.
- One person can have debts in two years; annual views show the corresponding debts, and the profile clearly shows both.
- Partial payments, payments covering multiple installments, and final payments produce the correct balance and status.
- Zero, negative, excessive amounts, and invalid dates produce clear messages without changing balances.
- The examples in section 5 produce the stated results, including rounding and a payment in the following year.
- Submitting the same form twice records one payment; two concurrent payments cannot overpay a debt.
- Voiding preserves the original payment, employee, and reason, and updates totals correctly.
- Annual summary cards match the sums for the entire year and remain unchanged when searching or changing table pages.
- Unauthenticated users cannot access records or record payments through direct requests.
- Empty years, settled debts, and people with multiple debts have clear displays; backup restoration is verified before production use.

## 9. Current project state

The repository now contains the preparation and data/calculation phases: login and logout, the `is_admin` column, administrator middleware, debtor, debt and payment models, validation, payment/voiding service, and calculation and access-control tests. PHP, Composer, Laravel Boost, and the frontend build are configured. Entry screens, the annual overview, and the debtor profile follow in the next step.
