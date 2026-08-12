import SwiftUI

struct ListsView: View {
    @Environment(AppSession.self) private var session
    @State private var state: Loadable<[ResearchList]> = .loading

    var body: some View {
        NavigationStack {
            LoadableView(state: state, retry: { Task { await load() } }) { lists in
                Group {
                    if lists.isEmpty {
                        ContentUnavailableView(
                            "No lists yet",
                            systemImage: "list.star",
                            description: Text("Ask your agent to start one: \"Put the most interesting AI infra companies on a watchlist.\"")
                        )
                    } else {
                        List(lists) { list in
                            NavigationLink(value: list) {
                                VStack(alignment: .leading, spacing: 4) {
                                    Text(list.name).font(.headline)
                                    HStack(spacing: 6) {
                                        Text("\(list.companiesCount ?? 0) companies")
                                        if let via = list.createdVia {
                                            Text("· \(via)")
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
            .navigationTitle("Lists")
            .navigationDestination(for: ResearchList.self) { list in
                ListDetailView(listId: list.id, title: list.name)
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
            state = .loaded(try await client.lists())
        } catch {
            state = .failed(error.localizedDescription)
        }
    }
}

struct ListDetailView: View {
    @Environment(AppSession.self) private var session
    let listId: Int
    let title: String
    @State private var state: Loadable<ListDetail> = .loading

    var body: some View {
        LoadableView(state: state, retry: { Task { await load() } }) { list in
            List {
                if let description = list.description, !description.isEmpty {
                    Section {
                        Text(description).foregroundStyle(.secondary)
                    }
                }

                Section {
                    ForEach(list.entries) { entry in
                        NavigationLink(value: entry.company.slug) {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(entry.company.name).font(.headline)
                                if let rationale = entry.rationale {
                                    Text(rationale)
                                        .font(.subheadline)
                                        .foregroundStyle(.secondary)
                                }
                                if let via = entry.createdVia {
                                    Text(via)
                                        .font(.caption2)
                                        .foregroundStyle(.tertiary)
                                }
                            }
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
            state = .loaded(try await client.list(id: listId))
        } catch {
            state = .failed(error.localizedDescription)
        }
    }
}
