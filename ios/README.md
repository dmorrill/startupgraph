# StartupGraph iOS

The read-only viewport onto your agent-built research layer: signals feed,
screens, lists, notes, and company profiles. See
[`docs/restart-plan-ios-first.md`](../docs/restart-plan-ios-first.md) for how
this fits the product.

## Requirements

- Xcode 15+ (iOS 17 deployment target)
- [XcodeGen](https://github.com/yonaskolb/XcodeGen): `brew install xcodegen`

## Getting started

```bash
cd ios
xcodegen            # generates StartupGraph.xcodeproj from project.yml
open StartupGraph.xcodeproj
```

The `.xcodeproj` is generated and gitignored — `project.yml` is the source of
truth, which keeps the project reviewable in PRs.

## Running against a backend

1. Start the backend: `php artisan serve` (or use the deployed URL).
2. Issue yourself a token: `php artisan api:token you@example.com --name=iphone`.
3. Launch the app, enter the server URL and token on the sign-in screen.

The simulator reaches a local `php artisan serve` at `http://127.0.0.1:8000`.

## Architecture

- **SwiftUI, iOS 17+, `@Observable`** view models, `NavigationStack`.
- `API/APIClient.swift` — thin async client over the JSON envelope API.
- `API/Models.swift` — `Codable` models matching the API resources 1:1.
- `Features/*` — one folder per tab: Signals (home feed), Screens, Lists,
  Search, plus the shared Company profile view.
- Token lives in the Keychain (`Support/Keychain.swift`).
- Read-only by design: v1 renders what agents produce; the only writes are
  marking signals read.
