# Changelogs:
2026-02-19 02:30:00 feature: Added SK Officials tables and forms, and updated Municipality and Barangay resources.
2026-02-19 02:45:00 fix: Resolved TypeError by updating Get and Set utility imports to Filament\Schemas\Components\Utilities.
2026-02-19 03:00:00 fix: Updated CatanduanesSeeder to handle zip code updates and ran the seeder to update database.
2026-02-19 09:44:33 fix: Redesigned homepage layout to match dashboard theme and fixed admin entry links.
2026-02-19 10:44:45 fix: Updated branding text to SKPrimeTech across homepage and admin UI.
2026-02-22 10:10:36 PM fix: Aligned DataInput navigationGroup type with Filament Page union type.
2026-02-22 10:14:15 PM fix: Updated ComplianceChart heading to be non-static per Filament ChartWidget.
2026-02-22 10:31:32 PM fix: Replaced missing BarangayItem class with FormSubmission in SystemStatsOverview and removed stale references.
2026-02-22 10:38:18 PM fix: Updated ActionRequiredWidget to use correct Filament\Actions\Action namespace instead of missing Filament\Tables\Actions\Action.
2026-02-23 12:03:58 AM feature: Renamed user accounts from 'SK Federation' to 'SKMF', fixed scope locking for municipal users, and refined dashboard widget visibility and duplicates.
2026-02-23 12:08:15 AM fix: Merged separate 'Forms' widget into main SystemStatsOverview for unified dashboard grid.
2026-02-23 12:35:00 AM feature: Implemented 'My Municipality' profile page, improved Form Builder UX with TagsInput for file types and descriptions, and added custom User Profile with avatar management.
2026-02-23 04:31:44 AM feature: Updated system/webapp color theme to brand palette (primary #1A508E, danger #AF1E21, warning #F4AD1D) and aligned Tailwind primary scale.
2026-02-23 04:58:48 AM feature: Reordered dashboard widgets to group submission charts, fixed sticky footer persistence, and disabled manual creation in Activity Logs.
2026-02-23 05:04:39 AM fix: Resolved duplicate 'Approved Submissions' widget heading by renaming ComplianceChart and adjusting sort order to prioritize Charts > Stats > Actions.
2026-02-23 05:17:16 AM feature: Added 'About Us' page in System Administration containing history, background, contact details, and social media links.
2026-02-23 05:21:17 AM fix: Corrected PHP property type signatures in AboutUs page to match Filament Page class requirements for navigationIcon and navigationGroup.
2026-03-09 06:20:55 PM fix: Corrected DataInput Grid import to Filament\Schemas\Components\Grid to resolve class not found error on conditional sub-question layout.
2026-03-09 06:29:11 PM feature: Added configurable Link/s limits in Form Definitions via dedicated Link Settings, and enforced those min/max link counts in Data Input and submission display.
2026-03-09 06:33:54 PM fix: Updated Data Input Link/s behavior to prefill the configured minimum number of link inputs and hide Add Link when max links are already reached by Form Definition settings.
2026-03-09 06:39:02 PM fix: Resolved Link/s repeater state type error by preventing scalar default assignment on link repeaters and disabled reordering for child link questions tied to parent conditions.
2026-03-23 10:01:51 AM fix: Updated Link/s child-question UI to append label text on the question title, removed inner link item label wrapping, and added strict server-side URL normalization and security validation rules.
2026-03-23 10:07:04 AM fix: Rendered single child Link/s questions as plain URL inputs without repeater wrappers while preserving normalized secure URL validation and storage compatibility.
2026-03-23 10:11:04 AM fix: Updated Data Entry layout to make each question row span full width and distribute parent-child fields evenly across row space.
2026-03-23 10:11:07 AM fix: Resolved 404 error on edit routes by adding `getRouteKeyName` to `FormSubmission`, `FormDefinition`, `Municipality`, and `Barangay` models to align with their respective Filament Resource's `getRecordRouteKeyName`.
2026-03-23 12:08:00 PM feature: Added a new dashboard monthly summary widget with current month KPI cards and a collapsible previous-month history, plus automated monthly snapshot persistence via scheduled `stats:capture-monthly` command.
2026-03-23 12:31:29 PM fix: Enforced monthly snapshot capture to completed prior months only and added per-user per-session caching for dashboard monthly summary stats to reduce repeat load latency.
2026-03-23 01:06:01 PM fix: Updated monthly snapshot command to gracefully refresh existing month records instead of throwing unique-constraint errors on repeated runs.
2026-03-23 01:51:58 PM fix: Removed Barangay GUID writes from the model and aligned barangay CSV export to schema columns so inserts no longer fail on missing barangays.guid.
2026-03-23 01:55:09 PM fix: Hardened GUID migrations with table/column existence guards and restored correct form_definitions GUID migration flow while keeping barangays GUID removed.
2026-03-23 02:22:46 PM feature: Added new artisan commands to reset municipality account passwords with generated output, clear the latest municipality or barangay submitted reports, and reset municipal profile customization settings to defaults.
2026-03-23 06:07:14 PM feature: Added February Monitoring CSV import command that creates or updates a compatible barangay monthly template and injects February municipality barangay submissions from CSV files.
2026-03-23 06:31:32 PM fix: Fixed Tailwind v4 dark mode behavior to obey Filament's class-based theme toggle over OS preference using @custom-variant dark and added missing dark classes to monthly summary widget.
2026-03-23 07:12:38 PM fix: Updated dashboard monthly summary widget to display the last completed month instead of the active month, set application timezone to Asia/Manila (GMT+8), and globally configured Filament datetime columns to 12-hour AM/PM format.
2026-03-23 07:21:00 PM fix: Explicitly applied 12-hour AM/PM string format ('M j, Y h:i A') to all created_at TextColumn usages across the system to forcefully override Filament's internal 24-hour default fallbacks.
2026-03-23 07:28:30 PM fix: Added month backfill support to February Monitoring CSV importer with `--month=YYYY-MM`, preserved approved status on re-import, and corrected imported submission timestamp attribution for monthly dashboard statistics.
2026-03-24 11:08:56 AM feature: Added a Form Definitions table duplicate action with collision-safe naming and regenerated unique field keys, plus delete confirmation checkbox support for optionally deleting associated submitted reports.
2026-03-24 05:06:26 PM feature: Updated Filament branding to use combimark SVG logos with dark-mode switching and smooth logo transitions.
2026-03-24 05:07:32 PM fix: Corrected combimark SVG path data spacing in the webapp asset.
2026-03-24 05:10:06 PM feature: Added a reusable SKPrime logo Blade component and applied it to the homepage header and footer.
2026-03-24 05:19:25 PM fix: Fixed SKPrime logo rendering in Filament by including component views in Tailwind sources and overlaying dark-mode logo for smooth switching.
2026-03-24 05:19:25 PM fix: Updated PolicyScopingTest to validate Barangay policy scoping after BarangayItem removal.
2026-03-30 06:31:09 PM feature: Added preset suggestions for Short Answer form fields while still allowing custom typed answers in Data Input.
2026-03-30 06:59:43 PM feature: Simplified Dropdown option authoring to one visible field with safe unique internal values and added custom clickable Short Answer suggestion chips in Data Input.
2026-03-31 10:51:08 AM fix: Added custom Filament login validation to show an explicit password error when email exists but password is incorrect.
2026-03-31 11:35:00 feature: Implement Shared Access Links (Public Viewing) feature. Added SharedAccessLink model, Filament resource for super admin/admin, and public Livewire page for viewing dashboards and records without auth.
2026-03-31 11:47:24 AM feature: Upgraded Shared Access Links into a Shared Access Portal with access tracking, scoped record-status visibility controls, improved admin management actions, and new `/access/{token}` route while keeping legacy `/shared/{token}` support.
2026-03-31 11:47:24 AM fix: Resolved public shared-link 500 error by aligning the Livewire table component with Filament v5 required actions and schemas interfaces/traits.
2026-03-31 04:36:04 PM feature: Implemented a full professional UI for the Shared Access Portal, including a branded navigation bar, hero banner, dark mode compatibility, styled content wrappers for charts and tables, and a responsive footer.
2026-03-31 04:50:48 PM fix: Resolved unstyled SVG icons and missing layout utility classes on the public portal by correctly registering global Filament colors and including Filament's core CSS for standalone Livewire tables.
2026-03-31 04:53:53 PM fix: Standardized Shared Access Portal footer to match the exact Filament admin dashboard footer design and layout.
2026-03-31 05:32:46 PM feature: Rewrote Shared Access Portal hero text to explicitly brand the page as a Public Dashboard for better user context and transparency.
2026-03-31 06:00:21 PM fix: Replaced the invalid Filament Tables action namespace on Shared Access records with the correct Filament Actions ViewAction and enabled row click-to-open submission detail modals.
2026-04-01 04:28:12 PM feature: Added SVG logo as global site favicon across welcome page, shared access portal, and Filament admin dashboard.
2026-04-01 04:48:00 PM fix: Optimized Shared Access Portal records table for mobile by wrapping text and stacking record types below names. Formatted barangay record names to append their municipality in parentheses.
2026-04-01 04:52:00 PM fix: Resolved modal overlay z-index issue on the Shared Access Portal by lowering the public navbar z-index so Filament modals correctly appear on top.
2026-04-08 11:03:51 AM fix Updated user credentials for super admin, admin, and municipal users to use the rubyj.xyz domain. Renamed credentials file to prime_creds.txt.
2026-04-08 11:58:55 AM feature Integrated Telegram Bot alerts via Cloudflare Worker for system errors, activity logs, and failed login attempts.
2026-04-08 12:44:54 PM feature Added artisan commands `reports:templates` and `reports:submissions` to fetch and output report templates and submitted reports to the terminal.
2026-04-08 12:56:00 PM fix Resolved issue where conditional sub-questions (e.g., Non-compliance reasons) were incorrectly visible in the admin submission review page regardless of the parent field's selected value. Applied schema visibility parsing to FormSubmissionForm.
2026-04-08 01:07:01 PM feature Added a quick View Details icon (modal) to each entry in the admin Form Submissions table, similar to the public portal experience.
2026-04-08 01:10:00 PM fix Resolved missing `ViewAction` class error in admin Form Submissions table by importing the correct `Filament\Tables\Actions` namespace.
2026-04-08 02:56:54 PM feature: Added "Form" (Report Type) and "Type" (Barangay/Municipality) filters to the admin Form Submissions table for improved data filtering.
2026-04-08 02:58:38 PM fix: Changed created_at timestamp format in Form Submissions table to mm/dd/yyyy hh:mm am/pm.
2026-04-08 03:17:26 PM feature: Added `reports:migrate-january-monitoring` command to duplicate February Monitoring into January Monitoring, remove the Facebook field, transform January CSV status/link data into hierarchical PPA fields, auto-set Non-compliant reasons to "No Post", auto-approve imported submissions, apply controlled submission/approval timestamp sequencing, and generate a detailed migration log file.
2026-04-08 03:29:11 PM fix: Updated January migration label transformation to remove month text from per-question labels (month retained only in template name) and added collision-safe submission GUID generation for reliable re-runs.
2026-04-08 03:41:41 PM feature: Exposed 'Date Created' column in Form Definitions (Report Templates) table and set format to mm/dd/yyyy hh:mm am/pm.
2026-04-08 04:12:18 PM fix: Updated 'Deadline' column format in Form Definitions (Report Templates) table to mm/dd/yyyy hh:mm am/pm.
[2026-04-08 04:24:45 PM] feature Forced 'Date Created' column to be visible in Report Templates table and ensured the format is mm/dd/yyyy hh:mm am/pm.
[2026-04-08 04:39:11 PM] feature Changed table actions per entry to icon only with tooltip in Form Definitions and Form Submissions tables.
[2026-04-08 05:19:17 PM] feature Implemented a modal showing records per municipality when clicking on a portion in the pie charts (ApprovedSubmissionsByMunicipalityChart and ItemsByMunicipalityChart).


[2026-04-08 05:26:27 PM] feature Updated pie chart modal title to include chart title + municipality name, and disabled autofocus on modal view actions to prevent scrolling to bottom.


[2026-04-08 05:36:22 PM] feature Updated Dashboard layout to a 1x3 grid for Approved Submissions, Submissions, and Compliance Overview charts while maintaining chart sizes.


[2026-04-08 05:45:14 PM] feature Moved Compliance Overview chart to the 1x3 top row grid along with pie charts and disabled its aspect ratio for uniform sizing.

[2026-04-08 05:52:37 PM] fix Fixed Compliance Overview chart height to match other cards by adding responsive option, and reordered charts: Submissions by Municipality (1st), Approved Submissions by Municipality (2nd), Compliance Overview (3rd).

[2026-04-08 05:55:35 PM] fix Fixed Compliance Overview widget height discrepancy and dashboard sorting.

[2026-04-08 06:00:45 PM] feature Added warning icon indicator (exclamation circle) in the deadline column when no deadline is set for report templates.

[2026-04-08 06:03:26 PM] fix Fixed deadline column icon to use raw SVG instead of Blade component for proper rendering.

[2026-04-11 10:43:54 AM] fix Resolved "unexpected integer" parse errors in ComplianceChart and ItemsByMunicipalityChart widgets caused by trailing digits on the `$sort` property.

[2026-04-11 10:50:04 AM] fix Resolved missing/invisible logo issue on macOS Safari by updating the `x-skprime-logo` component layout to use `inline-flex` instead of `inline-block` and ensuring explicit height inheritance (`h-full`) in the Filament brand wrapper.

[2026-04-11 11:10:25 AM] feature Implemented "Active Forms" dashboard widget restricted to super admin and admin. Displays forms, deadlines, scope, and total submission ratios with detailed progress modal. Includes performance optimization through caching listeners for `Barangay` and `Municipality` counts on login.

[2026-04-11 11:29:58 AM] feature Enhanced Active Forms widget with toggleable form name alphabetical sorting and default month sort, plus added comprehensive "All Barangays" progress view in the details modal.

[2026-04-11 11:52:19 AM] feature Consolidated and merged 30+ migration files into 13 base migrations, keeping schema perfectly aligned and cleaning up the migrations directory.

[2026-04-11 03:19:03 PM] docs Added `prevent-destructive-operations.md` rule to strictly prevent agents from running `migrate:fresh` or other data-destroying commands locally without explicit user consent.

[2026-04-11 03:23:06 PM] fix Updated `active-forms-progress.blade.php` to use `Cache::rememberForever()` instead of `Cache::get()` to auto-repopulate empty modals if the cache is unexpectedly cleared.

[2026-04-11 03:27:04 PM] feature Replaced the static "Active Forms" header with a dynamic `HtmlString` that includes a clickable, animating refresh icon button.

[2026-04-11 03:33:03 PM] fix Moved the custom HTML widget header from `getHeading()` to `$table->heading()` so the refresh button properly renders within a `TableWidget`.
[2026-04-11 03:59:32 PM] fix Fixed the Active Forms refresh icon loading state by separating the hover icon from the loading spinner and targeting the animation to `$refresh`.
[2026-04-11 04:06:59 PM] fix Rendered Form Submissions `created_at` as two smaller lines (date + time) to reduce the column width.
[2026-04-11 04:14:02 PM] fix Changed Form Submissions table rows to open View Details in a modal (like Shared Access records) instead of navigating to the edit page.
[2026-04-11 04:18:25 PM] fix Updated global Filament light-mode styling so blue primary buttons render white text across the system.
[2026-04-11 04:20:09 PM] fix Forced Form Submissions row clicks to stop using default record URL and trigger the View Details modal, while preserving Edit button navigation.
[2026-04-11 04:24:10 PM] feature Updated Shared Access Links table with truncated public URLs, icon-only tooltip row actions, a copy-link action with toast notifications, and moved token regeneration into the edit modal.
[2026-04-11 04:29:05 PM] fix Added a Submitted By filter to the Form Submissions table using a searchable preloaded relationship select.
[2026-04-11 04:39:54 PM] fix Improved February CSV importer compatibility by adding normalized municipality/barangay matching (including parenthetical names, Sta./Sto., Poblacion variants, and fuzzy fallback) for re-injection from `.ignore` data sources.
[2026-04-11 09:41:15 PM] fix Replaced all February and January submissions via re-import, enhanced February and January importers with district-aware barangay matching, and made January migration use February CSV barangay roster as canonical source for barangay-name alignment across both month datasets.
[2026-04-11 09:50:34 PM] fix Deduplicated the Form Submissions Submitted By filter options by using unique submitter names and filtering records via submitter relationship name matching.
[2026-04-11 09:59:20 PM] fix Prevented User edit from nulling passwords when left blank and ensured selected roles persist during account updates.
[2026-04-11 10:09:04 PM] feature Consolidated the About Us contact section into a unified grid for email, phone number, and address, and removed all social links except Facebook.
[2026-04-11 10:18:14 PM] feature Updated About Us contact layout to a 4-column structure, set socials block to a 1x4 grid, and widened the address area.
[2026-04-11 10:21:13 PM] feature Reformatted the About Us "Our History" content into paragraph blocks for improved readability while preserving the updated narrative.
[2026-04-11 10:25:55 PM] fix Cleaned deployment leftovers from project root, moved local artifacts into `.ignore/leftovers/`, and updated `.gitignore` and `.vercelignore` for Vercel deployment prep.
