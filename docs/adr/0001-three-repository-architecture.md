# Three-Repository Architecture

We are separating the business applications into three distinct repositories to maintain clear separation of concerns, flexibility, and maintainability. Since we are not using a custom domain, each application will be hosted on its respective platform's free-tier default URL.

1. **Admin Dashboard (This repository)**: Laravel PHP API and Livewire/Inertia Admin Dashboard. Hosted on Render (e.g., `inea-scents.onrender.com`). Exclusively for admins.
2. **Landing Page**: A no-code CMS (e.g. Webflow or Framer) to allow non-technical business owners/marketing to make frequent updates without developer intervention. Hosted on its platform's free URL (e.g., `inea-scents.webflow.io`).
3. **Mobile App**: A Flutter app for end customers to browse and book the perfume bar service. Hosted on a free URL (e.g., `inea-scents-app.web.app`) if deployed as Flutter Web, and shipped to iOS/Android app stores.
