# Flutter Environment Wiring via dart-define

The runtime `Environment.local/live` toggle and `kDebugMode` branching leaked environment decisions into source and hardcoded prod fallbacks (`https://inea-scents.onrender.com`). Per 12-Factor III (config in environment) and V (build/release/run), we replaced it with required `--dart-define=API_URL` at build time. Local convenience fallback exists only in debug without a define; release throws if `API_URL` is empty. Vercel passes `API_URL` per env (Preview→staging, Production→prod); `flutter run` passes it locally. Freezed generators are pinned and the `sed` workaround is removed; `api_sync.yml` now guards drift.

## Consequences
- No edit to switch backends. `lib/config/environment.dart` is deprecated; `core_providers.dart` resolves via `String.fromEnvironment`.
- Generator versions are locked (`freezed ^3.1.0`, `json_serializable ^6.9.0`, etc.). Changing them requires regenerating `lib/api/**`.
