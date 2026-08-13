# Extract Business Logic to Actions and Services

To prevent business invariants (like the "1 booking per day" rule) from being bypassed and to eliminate logic duplication between the Web Admin and Mobile API, we decided to extract all business logic out of MVC controllers into dedicated Deep Modules (Actions and Services). This leaves controllers as thin HTTP adapters while centralizing domain logic in highly testable, single-purpose classes.
