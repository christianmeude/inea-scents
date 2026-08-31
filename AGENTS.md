## Protocol for Agents

**CRITICAL RULE:** WHEN SOMETHING NEEDS FIXING IN THE CODEBASE AND THE USER ASKS A QUESTION ABOUT IT, DO NOT IMPLEMENT THE FIX RIGHT AWAY. 
1. ANSWER THE QUESTION FIRST.
2. TELL THE USER WHAT YOU WILL DO TO FIX IT.
3. ONLY THEN DO YOU SUGGEST TO DO THE FIX AND WAIT FOR PERMISSION.

## Communication Style

Default communication style is caveman `full`: terse, drop articles/filler/hedging, fragments OK, short synonyms, no tool-call narration, no decorative tables/emoji. Technical substance preserved, errors quoted exact. Switch levels with `/caveman lite|full|ultra|wenyan-*|off`; revert with `stop caveman` or `normal mode`. File writes, code, comments, commits, docs, and issue/PR bodies remain normal prose.

## Commit Messages

Use Conventional Commits, terse and exact. Subject: `<type>(<scope>): <imperative summary>` (scope optional). Types: `feat`, `fix`, `refactor`, `perf`, `docs`, `test`, `chore`, `build`, `ci`, `style`, `revert`. Imperative mood ("add", not "added"). ≤50 chars preferred, 72 hard cap, no trailing period. Body only when why is non-obvious, for breaking changes, migrations, or linked issues; wrap at 72 chars, bullets with `-`, reference with `Closes #`/`Refs #` at end. Never include filler ("This commit", "I/we/now"), AI attribution, emoji (unless project requires), or restating filename covered by scope. Always include body for breaking changes, security fixes, migrations, and reverts.

## Agent skills

### Issue tracker

Issues are tracked on GitHub. See `docs/agents/issue-tracker.md`.

### Triage labels

The default 5-label triage vocabulary is used. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context documentation layout. See `docs/agents/domain.md`.
