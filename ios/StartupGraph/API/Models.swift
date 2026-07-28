import Foundation

// Codable models matching the API resources 1:1 (snake_case JSON is
// bridged by the client's .convertFromSnakeCase strategy).

struct Envelope<T: Decodable>: Decodable {
    let data: T
}

struct Me: Decodable {
    let name: String
    let email: String
    let tokenName: String?
}

// MARK: - Company graph

struct Location: Decodable, Hashable {
    let city: String?
    let state: String?
    let country: String?

    var display: String? {
        [city, country].compactMap(\.self).joined(separator: ", ").nilIfEmpty
    }
}

struct CompanySummary: Decodable, Identifiable, Hashable {
    let slug: String
    let name: String
    let category: String?
    let categoryLabel: String?
    let location: Location?
    let currentHeadcount: Int?
    let totalFunding: Double?
    let totalFundingFormatted: String?
    let latestFundingDate: String?
    let fundingRoundsCount: Int?

    var id: String { slug }
}

struct CompanyDetail: Decodable {
    let slug: String
    let name: String
    let website: String?
    let description: String?
    let category: String?
    let categoryLabel: String?
    let foundedDate: String?
    let location: Location?
    let linkedinUrl: String?
    let currentHeadcount: Int?
    let productHighlights: [String]?
    let totalFunding: Double?
    let totalFundingFormatted: String?
    let fundingRoundsCount: Int?
    let peopleCount: Int?
    let fundingRounds: [FundingRound]?
    let people: [PersonSummary]?
    let headcountSnapshots: [HeadcountSnapshot]?
}

struct FundingRound: Decodable, Hashable {
    let roundType: String?
    let amount: Double?
    let amountFormatted: String?
    let currency: String?
    let announcedDate: String?
    let sourceUrl: String?
    let investors: [Investor]?
}

struct Investor: Decodable, Hashable {
    let name: String
    let slug: String?
    let type: String?
    let isLead: Bool?
}

struct PersonSummary: Decodable, Identifiable, Hashable {
    let slug: String
    let name: String
    let role: String?
    let isCurrent: Bool?
    let linkedinUrl: String?

    var id: String { slug }
}

struct HeadcountSnapshot: Decodable, Hashable {
    let headcount: Int
    let recordedDate: String?

    var date: Date? {
        recordedDate.flatMap { DateFormatter.apiDate.date(from: $0) }
    }
}

struct SearchResults: Decodable {
    let companies: [CompanySummary]
    let people: [PersonSummary]
}

// MARK: - Research layer

struct ResearchList: Decodable, Identifiable, Hashable {
    let id: Int
    let name: String
    let description: String?
    let companiesCount: Int?
    let createdVia: String?
    let updatedAt: String?
}

struct ListDetail: Decodable {
    struct Entry: Decodable, Identifiable {
        struct EntryCompany: Decodable, Hashable {
            let name: String
            let slug: String
            let category: String?
            let city: String?
            let country: String?
        }

        let company: EntryCompany
        let rationale: String?
        let addedAt: String?
        let createdVia: String?

        var id: String { company.slug }
    }

    let id: Int
    let name: String
    let description: String?
    let entries: [Entry]
}

struct ScreenSummary: Decodable, Identifiable, Hashable {
    let id: Int
    let name: String
    let description: String?
    let criteria: [String: String]?
    let resultCount: Int?
    let refreshedAt: String?
    let createdVia: String?
}

struct ScreenDetail: Decodable {
    let id: Int
    let name: String
    let description: String?
    let criteria: [String: String]?
    let resultCount: Int?
    let refreshedAt: String?
    let results: [ScreenCompany]?
}

struct ScreenCompany: Decodable, Identifiable, Hashable {
    let name: String
    let slug: String
    let category: String?
    let city: String?
    let country: String?
    let currentHeadcount: Int?
    let totalRaised: Double?
    let latestFundingDate: String?

    var id: String { slug }
}

struct Note: Decodable, Identifiable, Hashable {
    let id: Int
    let company: String?
    let companyName: String?
    let title: String?
    let body: String
    let createdVia: String?
    let createdAt: String?
}

struct Signal: Decodable, Identifiable, Hashable {
    let id: Int
    let type: String
    let title: String
    let body: String?
    let company: String?
    let companyName: String?
    let createdVia: String?
    let readAt: String?
    let createdAt: String?

    var isUnread: Bool { readAt == nil }

    var systemImage: String {
        switch type {
        case "funding_round": "banknote"
        case "headcount_change": "person.3"
        default: "sparkles"
        }
    }
}

// MARK: - Helpers

extension DateFormatter {
    static let apiDate: DateFormatter = {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        formatter.locale = Locale(identifier: "en_US_POSIX")
        return formatter
    }()
}

extension String {
    var nilIfEmpty: String? { isEmpty ? nil : self }

    /// "2026-07-28T11:00:00+00:00" → short local date for row subtitles.
    var shortDate: String {
        guard let date = ISO8601DateFormatter().date(from: self) else { return self }
        return date.formatted(date: .abbreviated, time: .omitted)
    }
}
