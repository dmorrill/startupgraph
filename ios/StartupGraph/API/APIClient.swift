import Foundation

enum APIError: LocalizedError {
    case invalidURL
    case unauthenticated
    case server(status: Int)

    var errorDescription: String? {
        switch self {
        case .invalidURL: "That doesn't look like a valid server URL."
        case .unauthenticated: "The token was rejected. Check it and try again."
        case .server(let status): "The server returned an error (\(status))."
        }
    }
}

/// Thin async client over the StartupGraph JSON API. Every response is
/// an envelope of the form { data, meta }.
struct APIClient {
    let baseURL: URL
    let token: String

    private var decoder: JSONDecoder {
        let decoder = JSONDecoder()
        decoder.keyDecodingStrategy = .convertFromSnakeCase
        return decoder
    }

    private func get<T: Decodable>(_ path: String, query: [URLQueryItem] = []) async throws -> T {
        try await request(path, method: "GET", query: query)
    }

    private func post<T: Decodable>(_ path: String) async throws -> T {
        try await request(path, method: "POST")
    }

    private func request<T: Decodable>(_ path: String, method: String, query: [URLQueryItem] = []) async throws -> T {
        var components = URLComponents(url: baseURL.appending(path: path), resolvingAgainstBaseURL: false)!
        if !query.isEmpty {
            components.queryItems = query
        }

        var request = URLRequest(url: components.url!)
        request.httpMethod = method
        request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        request.setValue("application/json", forHTTPHeaderField: "Accept")

        let (data, response) = try await URLSession.shared.data(for: request)
        let status = (response as? HTTPURLResponse)?.statusCode ?? 0

        switch status {
        case 200...299: break
        case 401: throw APIError.unauthenticated
        default: throw APIError.server(status: status)
        }

        return try decoder.decode(Envelope<T>.self, from: data).data
    }

    // MARK: - Endpoints

    func me() async throws -> Me {
        try await get("/api/me")
    }

    func signals(unreadOnly: Bool = false) async throws -> [Signal] {
        try await get("/api/signals", query: unreadOnly ? [URLQueryItem(name: "unread", value: "1")] : [])
    }

    @discardableResult
    func markSignalRead(id: Int) async throws -> Signal {
        try await post("/api/signals/\(id)/read")
    }

    func lists() async throws -> [ResearchList] {
        try await get("/api/lists")
    }

    func list(id: Int) async throws -> ListDetail {
        try await get("/api/lists/\(id)")
    }

    func screens() async throws -> [ScreenSummary] {
        try await get("/api/screens")
    }

    func screen(id: Int) async throws -> ScreenDetail {
        try await get("/api/screens/\(id)")
    }

    func notes(companySlug: String? = nil) async throws -> [Note] {
        try await get("/api/notes", query: companySlug.map { [URLQueryItem(name: "company", value: $0)] } ?? [])
    }

    func company(slug: String) async throws -> CompanyDetail {
        try await get("/api/companies/\(slug)")
    }

    func search(_ query: String) async throws -> SearchResults {
        try await get("/api/search", query: [URLQueryItem(name: "q", value: query)])
    }
}
