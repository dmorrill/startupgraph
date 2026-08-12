import Charts
import SwiftUI

/// The company profile: graph data (funding, headcount, people) plus the
/// user's own research notes on this company.
struct CompanyDetailView: View {
    @Environment(AppSession.self) private var session
    let slug: String

    @State private var state: Loadable<CompanyDetail> = .loading
    @State private var notes: [Note] = []

    var body: some View {
        LoadableView(state: state, retry: { Task { await load() } }) { company in
            List {
                header(company)

                if let highlights = company.productHighlights, !highlights.isEmpty {
                    Section("Product") {
                        ForEach(highlights, id: \.self) { highlight in
                            Label(highlight, systemImage: "checkmark.circle")
                                .font(.subheadline)
                        }
                    }
                }

                if !notes.isEmpty {
                    Section("Your research") {
                        ForEach(notes) { note in
                            VStack(alignment: .leading, spacing: 4) {
                                if let title = note.title {
                                    Text(title).font(.headline)
                                }
                                Text(note.body).font(.subheadline)
                                if let via = note.createdVia, let created = note.createdAt {
                                    Text("\(via) · \(created.shortDate)")
                                        .font(.caption2)
                                        .foregroundStyle(.tertiary)
                                }
                            }
                            .padding(.vertical, 2)
                        }
                    }
                }

                if let snapshots = company.headcountSnapshots, snapshots.count >= 2 {
                    Section("Headcount") {
                        Chart(snapshots.sorted { ($0.date ?? .distantPast) < ($1.date ?? .distantPast) }, id: \.self) { snapshot in
                            if let date = snapshot.date {
                                LineMark(x: .value("Date", date), y: .value("Headcount", snapshot.headcount))
                                AreaMark(x: .value("Date", date), y: .value("Headcount", snapshot.headcount))
                                    .opacity(0.1)
                            }
                        }
                        .frame(height: 160)
                        .padding(.vertical, 4)
                    }
                }

                if let rounds = company.fundingRounds, !rounds.isEmpty {
                    Section("Funding") {
                        ForEach(rounds, id: \.self) { round in
                            HStack {
                                VStack(alignment: .leading, spacing: 2) {
                                    Text(round.roundType ?? "Round").font(.subheadline.weight(.medium))
                                    if let investors = round.investors, !investors.isEmpty {
                                        Text(investors.map(\.name).joined(separator: ", "))
                                            .font(.caption)
                                            .foregroundStyle(.secondary)
                                            .lineLimit(1)
                                    }
                                }
                                Spacer()
                                VStack(alignment: .trailing, spacing: 2) {
                                    Text(round.amountFormatted ?? "—").font(.subheadline)
                                    if let date = round.announcedDate {
                                        Text(date).font(.caption).foregroundStyle(.secondary)
                                    }
                                }
                            }
                        }
                    }
                }

                if let people = company.people, !people.isEmpty {
                    Section("People") {
                        ForEach(people.filter { $0.isCurrent != false }) { person in
                            VStack(alignment: .leading, spacing: 2) {
                                Text(person.name).font(.subheadline.weight(.medium))
                                if let role = person.role {
                                    Text(role).font(.caption).foregroundStyle(.secondary)
                                }
                            }
                        }
                    }
                }
            }
        }
        .navigationTitle(slug)
        .navigationBarTitleDisplayMode(.inline)
        .task { await load() }
    }

    @ViewBuilder
    private func header(_ company: CompanyDetail) -> some View {
        Section {
            VStack(alignment: .leading, spacing: 8) {
                Text(company.name).font(.title2.bold())

                if let description = company.description {
                    Text(description).font(.subheadline).foregroundStyle(.secondary)
                }

                HStack(spacing: 12) {
                    if let label = company.categoryLabel {
                        Text(label)
                            .font(.caption.weight(.medium))
                            .padding(.horizontal, 8)
                            .padding(.vertical, 3)
                            .background(.tint.opacity(0.12), in: Capsule())
                    }
                    if let location = company.location?.display {
                        Label(location, systemImage: "mappin").font(.caption)
                    }
                    if let headcount = company.currentHeadcount {
                        Label("\(headcount)", systemImage: "person.2").font(.caption)
                    }
                    if let funding = company.totalFundingFormatted {
                        Label(funding, systemImage: "banknote").font(.caption)
                    }
                }
                .foregroundStyle(.secondary)

                if let website = company.website, let url = URL(string: website) {
                    Link(destination: url) {
                        Label(website, systemImage: "globe").font(.caption)
                    }
                }
            }
            .padding(.vertical, 4)
        }
    }

    private func load() async {
        guard let client = session.client else { return }
        do {
            state = .loaded(try await client.company(slug: slug))
            notes = (try? await client.notes(companySlug: slug)) ?? []
        } catch {
            state = .failed(error.localizedDescription)
        }
    }
}
