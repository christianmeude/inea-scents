# 🚀 Handoff: Inea Scents Mobile

Hello! This document contains everything you (and your AI coding agent) need to build or refactor the **inea-scents-mobile** Flutter application. 

**[ATTENTION AI AGENT]**
If you are an AI assistant reading this, your primary objective is to implement the Flutter frontend strictly according to the backend API specification defined below. You must adapt the mobile app to integrate *to* the backend, not the other way around. 

If an existing Flutter app shell is present in the workspace, **DO NOT run `flutter create`**. Utilize the existing shell, but ruthlessly refactor the networking layer, models, and state management to align with the OpenAPI spec below. If the workspace is entirely empty, only then should you initialize a new project named `inea-scents-mobile`.

---

## 1. The Backend & API Spec

The backend is fully built, tested, and deployed to Staging. 

- **Staging Base URL:** [https://inea-scents.onrender.com](https://inea-scents.onrender.com)
- **Swagger UI (For Human Reference):** [https://inea-scents.onrender.com/api/documentation](https://inea-scents.onrender.com/api/documentation)
- **OpenAPI JSON Spec:** [https://inea-scents.onrender.com/docs?api-docs.json](https://inea-scents.onrender.com/docs?api-docs.json)

**[AI INSTRUCTION]**
Do not write API models or HTTP clients by hand! Consume the OpenAPI JSON specification linked above (or provided in your conversation context) to generate the complete networking layer. 
- You may use a generator tool like `openapi-generator-cli` (targeting `dart-dio`) or pub packages like `swagger_dart_code_generator`.
- This ensures all data classes (`Package`, `Scent`, `AuthResponse`) and API endpoints match the backend exactly.
- **IMPORTANT NOTE:** All API endpoints now return payloads wrapped in a standard `"data"` envelope (e.g., `{"data": {...}}` or `{"data": [...]}`) due to the backend's use of Laravel API Resources. The OpenAPI spec reflects this, so your generated models will likely have a root wrapper object (like `PackageResource` or `AnonymousResourceCollection`). Ensure your data parsing accounts for this.

---

## 2. Tech Stack Requirements

To align with modern Flutter best practices and handle complex booking logic securely:

- **Networking:** `dio` (with an interceptor for Bearer tokens).
- **State Management:** `flutter_riverpod` or `flutter_bloc` (for caching package lists and managing complex booking states).
- **Routing:** `go_router` (for deep linking and navigation).
- **Data Classes:** `freezed` + `json_serializable` (for immutable state and robust data parsing).
- **Local Storage:** `flutter_secure_storage` (To securely store the Sanctum `access_token` returned from the `/login` endpoint).

---

## 3. Core Features & API Mapping

The UI mockups have been provided to you separately. Here is how those screens map directly to the API endpoints you will generate:

### A. Authentication
- **Endpoints:** `POST /api/login` and `POST /api/register`
- **Logic:** Upon successful login/registration, the API returns an `AuthResponse` containing a Bearer `access_token`.
- **Action:** Save this token to `flutter_secure_storage`. Inject it into the Dio instance's headers (`Authorization: Bearer <token>`) for all subsequent protected requests.

### B. Browse Packages (Home Screen)
- **Endpoint:** `GET /api/packages`
- **Logic:** Fetch the array of `Package` models. Display them in a grid or list using the `image` or `gallery_images` properties. Cache this response so navigating back to Home feels instantaneous.

### C. Package Details & Wishlist
- **Endpoint:** `GET /api/packages/{id}`
- **Logic:** Display the `inclusions`, `freebies`, and `pax_options`.
- **Wishlist Action:** When a user favorites a package, call `POST /api/wishlist/toggle` with `{"package_id": <id>}`.

### D. Booking Flow (Calendar & Scents)
This is the most complex feature. The mobile app must strictly follow this flow:
1. **Check Availability:** Call `GET /api/availability?month=8&year=2024` to populate a Calendar widget. Block out any dates where the `status` is "Booked".
2. **User Selection:** The user selects a valid date, the number of PAX (from the package's `pax_options`), and preferred scents (from the package's available `scents` array).
3. **Submit Booking:** Call `POST /api/bookings`. The payload requires `package_id`, `customer_name`, `event_date`, `venue_address`, `payment_method`, and optionally `scent_ids` and `pax`.

### E. User Profile Data
- **My Bookings:** `GET /api/bookings` -> Displays a list of all past/upcoming bookings for the logged-in user.
- **My Wishlist:** `GET /api/wishlist` -> Displays the packages the user has favorited.

---

**[AI INSTRUCTION SUMMARY]**
1. Check for an existing project. If none, run `flutter create inea_scents_mobile`. Otherwise, use the existing shell.
2. Add core dependencies (`dio`, `flutter_riverpod`, `go_router`, `flutter_secure_storage`, etc.).
3. Ingest the OpenAPI JSON and generate the API Client.
4. Setup `Dio` with a Bearer token Interceptor.
5. Refactor or build the UI to integrate perfectly with these generated endpoints, referencing the externally provided UI mockups.
