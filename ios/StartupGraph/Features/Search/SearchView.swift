import SwiftUI

struct SearchView: View {
    @Environment(AppSession.self) private var session
    @State private var query = ""
    @State private var results: SearchResults?
    @State private var errorMessage: String?

    var body: some View {
        NavigationStack {
            List {
                if let results {
                    if !results.companies.isEmpty {
                        Section("Companies") {
                            ForEach(results.companies) { company in
                                NavigationLink(value: company.slug) {
                                    CompanyRow(
                                        name: company.name,
                                        subtitle: company.location?.display ?? "",
                                        headcount: company.currentHeadcount,
                                        raised: company.totalFunding
                                    )
                                }
                            }
                        }
                    }

                    if !results.people.isEmpty {
                        Section("People") {
                            ForEach(results.people) { person in
                                VStack(alignment: .leading, spacing: 2) {
                                    Text(person.name).font(.headline)
                                    if let role = person.role {
                                        Text(role).font(.caption).foregroundStyle(.secondary)
                                    }
                                }
                            }
                        }
                    }

                    if results.companies.isEmpty, results.people.isEmpty {
                        ContentUnavailableView.search(text: query)
                    }
                } else if let errorMessage {
                    Text(errorMessage).foregroundStyle(.red)
                } else {
                    ContentUnavailableView(
                        "Search 70,000 startups",
                        systemImage: "magnifyingglass",
                        description: Text("Company names, descriptions, cities, people.")
                    )
                }
            }
            .navigationTitle("Search")
            .searchable(text: $query, prompt: "Company or person")
            .navigationDestination(for: String.self) { slug in
                CompanyDetailView(slug: slug)
            }
            .task(id: query) {
                await search()
            }
        }
    }

    private func search() async {
        guard let client = session.client, query.count >= 2 else {
            results = nil
            return
        }

        // Small debounce so we don't hit the API on every keystroke.
        try? await Task.sleep(for: .milliseconds(300))
        guard !Task.isCancelled else { return }

        do {
            results = try await client.search(query)
            errorMessage = nil
        } catch is CancellationError {
            // superseded by a newer query
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}
