# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
- **Customers**: Users who book the perfume bar service via the mobile app.
- **Admins**: Business owners or managers who operate the web dashboard to manage bookings, packages, and settings.

## Product Purpose
A perfume bar service reservation system. Success means every date in the calendar is booked and every event garners excellent feedback and ratings.

## Positioning
Offers quality products and excellent service. It exudes elegance and grace, while being generous with the packages and services provided.

## Operating Context
Admins use a web dashboard to manage the business (bookings, packages, settings). Customers use a separate native Flutter mobile app to make and view bookings. Both applications communicate with this shared backend and database.

## Capabilities and Constraints
The mobile app is built in Flutter and uses this same backend and database, meaning this Laravel backend must reliably support API consumption for the mobile app alongside serving the admin web dashboard. The mobile app itself is out of scope for this repository.

## Brand Commitments
- Core aesthetic values: Elegance and grace
- Existing Facebook business page: https://www.facebook.com/profile.php?id=61580331093927

## Evidence on Hand
- Web dashboard prototypes (`docs/design/web/`)
- Mobile app prototypes (`docs/design/mobile/`)
- Palette inspiration (`docs/design/palette-inspo.png`)

## Product Principles
- **API First & Shared State**: The backend must seamlessly and consistently support both the web admin dashboard and the external mobile API.
- **Elegance in Utility**: The admin dashboard should reflect the brand's core values of elegance and grace, even as an internal tool.
- **Booking Efficiency**: Streamline booking and package management to help the business achieve a fully booked calendar and maintain excellent service quality.
