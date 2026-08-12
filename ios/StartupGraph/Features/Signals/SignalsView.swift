import SwiftUI

/// The home feed: funding rounds and headcount changes on followed
/// companies, plus anything agents logged with log_signal.
struct SignalsView: View {
    @Environment(AppSession.self) private var session
    @State private var state: Loadable<[Signal]> = .loading
    @State private var unreadOnly = false

    var body: some View {
        NavigationStack {
            LoadableView(state: state, retry: { Task { await load() } }) { signals in
                Group {
                    if signals.isEmpty {
                        ContentUnavailableView(
                            "No signals yet",
                            systemImage: "bolt.slash",
                            description: Text("Follow companies or ask your agent to log what it finds.")
                        )
                    } else {
                        List(signals) { signal in
                            SignalRow(signal: signal)
                                .task {
                                    if signal.isUnread {
                                        try? await session.client?.markSignalRead(id: signal.id)
                                    }
                                }
                        }
                        .refreshable { await load() }
                    }
                }
            }
            .navigationTitle("Feed")
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    Toggle("Unread", isOn: $unreadOnly)
                        .toggleStyle(.button)
                }
                ToolbarItem(placement: .topBarTrailing) {
                    SignOutMenu()
                }
            }
            .navigationDestination(for: String.self) { slug in
                CompanyDetailView(slug: slug)
            }
            .task(id: unreadOnly) { await load() }
        }
    }

    private func load() async {
        guard let client = session.client else { return }
        do {
            state = .loaded(try await client.signals(unreadOnly: unreadOnly))
        } catch {
            state = .failed(error.localizedDescription)
        }
    }
}

private struct SignalRow: View {
    let signal: Signal

    var body: some View {
        NavigationLink(value: signal.company ?? "") {
            HStack(alignment: .top, spacing: 12) {
                Image(systemName: signal.systemImage)
                    .foregroundStyle(signal.isUnread ? Color.accentColor : .secondary)
                    .frame(width: 24)

                VStack(alignment: .leading, spacing: 4) {
                    Text(signal.title)
                        .font(.subheadline.weight(signal.isUnread ? .semibold : .regular))
                    HStack(spacing: 6) {
                        if let created = signal.createdAt {
                            Text(created.shortDate)
                        }
                        if let via = signal.createdVia {
                            Text("· \(via)")
                        }
                    }
                    .font(.caption)
                    .foregroundStyle(.secondary)
                }
            }
        }
        .disabled(signal.company == nil)
    }
}

struct SignOutMenu: View {
    @Environment(AppSession.self) private var session

    var body: some View {
        Menu {
            if let name = session.me?.name {
                Text(name)
            }
            Button("Sign out", role: .destructive) {
                session.signOut()
            }
        } label: {
            Image(systemName: "person.circle")
        }
    }
}
