# Use API Resources for Mobile Boundaries

Because returning raw Eloquent models from the Mobile API controllers leaks the underlying database schema and risks exposing sensitive columns, we decided to use Laravel API Resources as strict translation boundaries. This acts as an explicit whitelist for mobile payloads and automatically provides a standard `data` envelope to support future metadata and pagination.
