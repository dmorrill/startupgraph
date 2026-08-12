import SwiftUI

struct MainTabView: View {
    var body: some View {
        TabView {
            SignalsView()
                .tabItem { Label("Feed", systemImage: "bolt.fill") }

            ScreensView()
                .tabItem { Label("Screens", systemImage: "line.3.horizontal.decrease.circle") }

            ListsView()
                .tabItem { Label("Lists", systemImage: "list.star") }

            SearchView()
                .tabItem { Label("Search", systemImage: "magnifyingglass") }
        }
    }
}

/// Shared load-state wrapper so every tab handles loading/error/empty
/// the same way.
enum Loadable<T> {
    case loading
    case loaded(T)
    case failed(String)
}

struct LoadableView<T, Content: View>: View {
    let state: Loadable<T>
    let retry: () -> Void
    @ViewBuilder let content: (T) -> Content

    var body: some View {
        switch state {
        case .loading:
            ProgressView().frame(maxWidth: .infinity, maxHeight: .infinity)
        case .failed(let message):
            ContentUnavailableView {
                Label("Couldn't load", systemImage: "wifi.exclamationmark")
            } description: {
                Text(message)
            } actions: {
                Button("Retry", action: retry)
            }
        case .loaded(let value):
            content(value)
        }
    }
}
