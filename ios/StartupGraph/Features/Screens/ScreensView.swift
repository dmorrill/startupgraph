import SwiftUI

struct ScreensView: View {
    @Environment(AppSession.self) private var session
    @State private var state: Loadable<[ScreenSummary]> = .loading

    var body: some View {
        NavigationStack {
            LoadableView(state: state, retry: { Task { await load() } }) { screens in
                Group {
                    if screens.isEmpty {
                        ContentUnavailableView(
                            "No screens yet",
                            systemImage: "line.3.horizontal.decrease.circle",
                            description: Text("Ask your agent to create one: \"Build me a screen of recently funded dev-tools companies.\"")
                        )
                    } else {
                        List(screens) { screen in
                            NavigationLink(value: screen) {
                                VStack(alignment: .leading, spacing: 4) {
                                    Text(screen.name).font(.headline)
                                    HStack(spacing: 6) {
                                        if let count = screen.resultCount {
                                            Text("\(count) companies")
                                        }
                                        if let refreshed = screen.refreshedAt {
                                            Text("· refreshed \(refreshed.shortDate)")
                                        }
                                    }
                                    .font(.caption)
                                    .foregroundStyle(.secondary)
                                }
                            }
                        }
                        .refreshable { await load() }
                    }
                }
            }
            .navigationTitle("Screens")
            .navigationDestination(for: ScreenSummary.self) { screen in
                ScreenDetailView(screenId: screen.id, title: screen.name)
            }
            .navigationDestination(for: String.self) { slug in
                CompanyDetailView(slug: slug)
            }
            .task { await load() }
        }
    }

    private func load() async {
        guard let client = session.client else { return }
        do {
            state = .loaded(try await client.screens())
        } catch {
            state = .failed(error.localizedDescription)
        }
    }
}

struct ScreenDetailView: View {
    @Environment(AppSession.self) private var session
    let screenId: Int
    let title: String
    @State private var state: Loadable<ScreenDetail> = .loading

    var body: some View {
        LoadableView(state: state, retry: { Task { await load() } }) { screen in
            List {
                if let criteria = screen.criteria, !criteria.isEmpty {
                    Section("Criteria") {
                        ForEach(criteria.sorted(by: { $0.key < $1.key }), id: \.key) { key, value in
                            LabeledContent(key, value: value)
                        }
                    }
                }

                Section("Results") {
                    ForEach(screen.results ?? []) { company in
                        NavigationLink(value: company.slug) {
                            CompanyRow(
                                name: company.name,
                                subtitle: [company.city, company.country].compactMap(\.self).joined(separator: ", "),
                                headcount: company.currentHeadcount,
                                raised: company.totalRaised
                            )
                        }
                    }
                }
            }
        }
        .navigationTitle(title)
        .task { await load() }
    }

    private func load() async {
        guard let client = session.client else { return }
        do {
            state = .loaded(try await client.screen(id: screenId))
        } catch {
            state = .failed(error.localizedDescription)
        }
    }
}

struct CompanyRow: View {
    let name: String
    let subtitle: String
    var headcount: Int?
    var raised: Double?

    var body: some View {
        VStack(alignment: .leading, spacing: 4) {
            Text(name).font(.headline)
            HStack(spacing: 8) {
                if !subtitle.isEmpty {
                    Text(subtitle)
                }
                if let headcount {
                    Label("\(headcount)", systemImage: "person.2")
                }
                if let raised, raised > 0 {
                    Label(raised.compactCurrency, systemImage: "banknote")
                }
            }
            .font(.caption)
            .foregroundStyle(.secondary)
        }
    }
}

extension Double {
    /// 1_500_000_000 → "$1.5B"
    var compactCurrency: String {
        switch self {
        case 1_000_000_000...: String(format: "$%.1fB", self / 1_000_000_000)
        case 1_000_000...: String(format: "$%.1fM", self / 1_000_000)
        case 1_000...: String(format: "$%.0fK", self / 1_000)
        default: String(format: "$%.0f", self)
        }
    }
}
